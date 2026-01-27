<?php

namespace Lyre\Commerce\Services;

use Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface;
use Lyre\Commerce\Repositories\Contracts\ShippingAddressRepositoryInterface;
use Lyre\Commerce\Repositories\Contracts\OrderItemRepositoryInterface;
use Lyre\Billing\Services\Mpesa\Client as MpesaClient;
use Lyre\Billing\Services\Paypal\Client as PaypalClient;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected PricingService $pricing,
        protected CouponService $coupons,
        protected ShippingAddressRepositoryInterface $shippingAddresses,
        protected OrderItemRepositoryInterface $orderItems,
    ) {}

    public function confirmShipping(array $payload)
    {
        $order = $this->getPendingOrder();
        // Accept existing shipping_address_id or create from payload
        if (isset($payload['shipping_address_id'])) {
            $order->shipping_address_id = (int) $payload['shipping_address_id'];
        } else {
            // If delivery_address is provided, use it as address_line_1
            if (isset($payload['delivery_address']) && !isset($payload['address_line_1'])) {
                $payload['address_line_1'] = $payload['delivery_address'];
            }
            $address = $this->shippingAddresses->create($payload);
            $order->shipping_address_id = $address->resource->id;
        }
        if (isset($payload['location_id'])) {
            $order->location_id = (int) $payload['location_id'];
        }
        // Store delivery_address in order metadata if provided
        if (isset($payload['delivery_address'])) {
            $metadata = $order->metadata ?? [];
            $metadata['delivery_address'] = $payload['delivery_address'];
            $order->metadata = $metadata;
        }
        $order = $this->pricing->computeTotals($order->fresh('items', 'location'));
        $order->status = 'confirmed';
        if (!$order->reference) {
            $order->reference = (string) Str::uuid();
        }
        $order->save();
        return $order->load('items', 'shippingAddress', 'location');
    }

    public function confirmOrder(int|string $orderId)
    {
        // Handle both ID and slug/reference
        $order = null;
        if (is_numeric($orderId)) {
            $order = $this->orders->find($orderId)->resource;
        } else {
            // Try to find by reference or slug
            $orderModel = $this->orders->getModel();
            $order = $orderModel::where('reference', $orderId)
                ->orWhere('id', $orderId)
                ->first();
            if (!$order) {
                throw new \Exception("Order not found with ID/reference: {$orderId}");
            }
        }
        $order->status = 'confirmed';
        if (!$order->reference) {
            $order->reference = (string) Str::uuid();
        }
        $order->save();
        return $order;
    }

    public function invoice(int|string $orderId)
    {
        // Optional: integrate InvoiceRepository if needed. For now recompute totals and mark invoiced
        $order = null;
        if (is_numeric($orderId)) {
            $order = $this->orders->find($orderId)->resource;
        } else {
            // Try to find by reference or slug
            $orderModel = $this->orders->getModel();
            $order = $orderModel::where('reference', $orderId)
                ->orWhere('id', $orderId)
                ->first();
            if (!$order) {
                throw new \Exception("Order not found with ID/reference: {$orderId}");
            }
        }
        $order = $this->pricing->computeTotals($order->fresh('items', 'location'));
        $order->status = 'invoiced';
        $order->save();
        return $order;
    }

    public function pay(int|string $orderId, array $payload)
    {
        $order = null;
        if (is_numeric($orderId)) {
            $order = $this->orders->find($orderId)->resource;
        } else {
            // Try to find by reference or slug
            $orderModel = $this->orders->getModel();
            $order = $orderModel::where('reference', $orderId)
                ->orWhere('id', $orderId)
                ->first();
            if (!$order) {
                throw new \Exception("Order not found with ID/reference: {$orderId}");
            }
        }

        $paymentMethod = $payload['payment_method'] ?? 'mpesa';
        $amount = $order->total_amount;

        // Ensure order has reference for transaction linking
        if (!$order->reference) {
            $order->reference = (string) Str::uuid();
            $order->save();
        }

        // MPESA payment
        if (strtolower($paymentMethod) === 'mpesa') {
            $phone = $payload['phone'] ?? $payload['phone_number'] ?? null;
            if (!$phone) {
                throw new \Exception('Phone number required for Mpesa payment');
            }
            $mpesa = app(MpesaClient::class);
            
            // Include order reference for webhook linking
            $response = $mpesa->express(
                partyA: $payload['party_a'] ?? null,
                phoneNumber: $phone,
                amount: $amount,
                paymentMethod: null,
                orderReference: $order->reference
            );
        }
        // PayPal payment
        elseif (strtolower($paymentMethod) === 'paypal') {
            $paypal = new \Lyre\Billing\Services\Paypal\Payment();
            $response = $paypal->create($order, $payload);
            
            // PayPal requires user approval, so order stays as invoiced until capture
            return $response;
        } else {
            throw new \Exception("Unsupported payment method: {$paymentMethod}");
        }

        // Based on payment terms, determine fulfillment readiness
        $behavior = config('commerce.payment_terms.' . ($payload['payment_term_reference'] ?? 'prepaid'), 'require_payment_before_fulfillment');
        if (in_array($behavior, ['fulfillment_before_payment', 'partial_payment_then_fulfillment'])) {
            $order->status = 'ready_for_fulfillment';
            $order->save();
        }
        // For prepaid, keep as invoiced until webhook sets to paid
        return $response ?? ['status' => 'initiated'];
    }

    private function getPendingOrder()
    {
        $orderModel = $this->orders->getModel();
        $order = $orderModel::query()
            ->where('customer_id', auth()->id())
            ->where('status', 'pending')
            ->first();
        if (!$order) {
            throw new \Exception('No pending order');
        }
        return $order;
    }
}


