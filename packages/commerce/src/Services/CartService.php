<?php

namespace Lyre\Commerce\Services;

use Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface;
use Lyre\Commerce\Repositories\Contracts\OrderItemRepositoryInterface;
use Lyre\Commerce\Repositories\Contracts\ProductVariantRepositoryInterface;
use Lyre\Commerce\Repositories\Contracts\UserProductVariantRepositoryInterface;

class CartService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected OrderItemRepositoryInterface $orderItems,
        protected ProductVariantRepositoryInterface $variants,
        protected UserProductVariantRepositoryInterface $userVariants,
        protected PricingService $pricing,
    ) {}

    public function add(array $payload)
    {
        // Support both product_variant_id (direct) and product_id (with default variant)
        $variantId = ($payload['product_variant_id'] ?? 0);
        $productId = $payload['product_id'] ?? null;
        $quantity = max(1, (int) ($payload['quantity'] ?? 1));

        $order = $this->getOrCreatePendingOrder();

        // If product_id is provided but no variant_id, use default variant
        if (!$variantId && $productId) {
            $productModel = \Lyre\Commerce\Models\Product::where('slug', $productId)
                ->orWhere('id', $productId)
                ->first();

            if ($productModel) {
                $productModel->load('variants.userProductVariants.prices');
                $defaultVariant = $productModel->default_variant;
                if ($defaultVariant) {
                    $variantId = $defaultVariant->id;
                } else {
                    // Fallback to first enabled variant
                    $firstVariant = $productModel->variants->where('enabled', true)->first();
                    if ($firstVariant) {
                        $variantId = $firstVariant->id;
                    }
                }
            }
        }

        if (!$variantId) {
            throw new \InvalidArgumentException('product_variant_id or product_id is required');
        }

        $variant = $this->variants->find($variantId)->resource;

        if (!$variant) {
            throw new \InvalidArgumentException("Product variant with ID {$variantId} not found");
        }

        $tenant = tenant();
        if ($tenant) {
            // Find UserProductVariant for the current merchant and this variant
            $userVariant = $this->userVariants->find([
                'product_variant_id' => $variant->id,
                'user_id' => $tenant->user_id
            ])->resource;

            $priceRow = null;
            if ($userVariant) {
                $priceRow = $userVariant->prices()->latest()->first();
            }

            $unitPrice = $priceRow?->price ?? 0;
            $currency = $priceRow?->currency ?? config('commerce.default_currency', 'KES');
        } else {
            $unitPrice = $variant->price ?? 0;
            $currency = $variant->currency ?? config('commerce.default_currency', 'KES');
        }

        // Upsert item in order
        $existing = $order->items()->where('product_variant_id', $variant->id)->first();
        if ($existing) {
            $existing->quantity += $quantity;
            $existing->unit_price = $unitPrice;
            $existing->subtotal = $existing->quantity * $existing->unit_price;
            $existing->currency = $currency;
            $existing->save();
        } else {
            $order->items()->create([
                'product_variant_id' => $variant->id,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $unitPrice * $quantity,
                'currency' => $currency,
                'snapshot' => [
                    'variant' => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                    ],
                    'price' => $priceRow?->toArray(),
                ],
            ]);
        }

        $order = $this->pricing->computeTotals($order->fresh('items', 'location'));
        $order->save();

        return $order->load('items');
    }

    public function remove(array $payload)
    {
        $variantId = $payload['product_variant_id'] ?? null;

        if (! $variantId) {
            throw new \InvalidArgumentException('product_variant_id is required');
        }

        $variant = $this->variants->find($variantId)->resource;

        $order = $this->getOrCreatePendingOrder();

        $item = $order->items()
            ->where('product_variant_id', $variant->id)
            ->first();

        if (! $item) {
            return $order->load('items'); // nothing to remove
        }

        // If quantity is NOT provided → remove all (current behavior)
        if (! array_key_exists('quantity', $payload)) {
            $item->delete();
        } else {
            $quantity = (int) $payload['quantity'];

            if ($quantity < 1) {
                throw new \InvalidArgumentException('quantity must be a positive integer');
            }

            // If requested quantity >= existing quantity → remove item
            if ($quantity >= $item->quantity) {
                $item->delete();
            } else {
                // Reduce quantity
                $item->quantity -= $quantity;
                $item->subtotal = $item->quantity * $item->unit_price;
                $item->save();
            }
        }

        $order = $this->pricing->computeTotals(
            $order->fresh('items', 'location')
        );

        $order->save();

        return $order->load('items');
    }


    public function summary()
    {
        if (!auth()->check()) {
            throw new \RuntimeException('User must be authenticated to access cart');
        }

        return $this->getOrCreatePendingOrder()->load('items', 'location', 'shippingAddress');
    }

    public function applyCoupon(string $code)
    {
        $order = $this->getOrCreatePendingOrder();
        // Simply associate coupon; detailed discounting can occur in PricingService
        $coupon = app(CouponService::class)->validateAndResolve($code);
        $order->coupon_id = $coupon->id;
        $order = $this->pricing->computeTotals($order->fresh('items', 'location'));
        $order->save();
        return $order->load('items');
    }

    public function removeCoupon()
    {
        $order = $this->getOrCreatePendingOrder();
        $order->coupon_id = null;
        $order = $this->pricing->computeTotals($order->fresh('items', 'location'));
        $order->save();
        return $order->load('items');
    }

    private function getOrCreatePendingOrder()
    {
        $customerId = auth()->id();

        if (!$customerId) {
            throw new \RuntimeException('User must be authenticated to access cart');
        }

        $orderModel = $this->orders->getModel();

        $order = $this->orders
            ->silent()
            ->find([
                'customer_id' => $customerId,
                'status' => 'pending',
            ])?->resource;

        if (!$order) {
            $order = $orderModel::create([
                'customer_id' => $customerId,
                'status' => 'pending',
                'reference' => self::generateOrderReference(),
                'amount' => 0,
                'total_amount' => 0,
            ]);
        }

        return $order;
    }

    private static function generateOrderReference($tenant = null, ?\DateTimeInterface $date = null): string
    {
        $tenant = $tenant ?? tenant();

        $name = '';
        if ($tenant) {
            $name = $tenant->name ?? $tenant->title ?? $tenant->slug ?? '';
        }

        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', (string) $name) ?: (string) $name;
        $words = preg_split('/\s+/', trim($trans));
        $initials = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $initials .= mb_substr($word, 0, 1);
            }
        }

        $initials = substr(strtoupper($initials . 'XX'), 0, 2);

        $tenantId = (int) ($tenant->id ?? 0);
        $hashChar = strtoupper(base_convert((string) ($tenantId % 36), 10, 36));

        $tenantSegment = strtoupper($initials) . $hashChar;

        $date = $date ? \Carbon\Carbon::instance($date) : now();
        $dateSegment = $date->format('ymd');

        $orderModel = orderRepository()->getModel();
        $like = sprintf('%s-%s-%%', $tenantSegment, $dateSegment);

        $count = $orderModel::query()->where('reference', 'like', $like)->count();

        $sequence = str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);

        return sprintf('%s-%s-%s', $tenantSegment, $dateSegment, $sequence);
    }
}
