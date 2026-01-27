<?php

namespace Lyre\Commerce\Services;

use Lyre\Commerce\Models\Order;

class PricingService
{
    public function computeTotals(Order $order): Order
    {
        $amount = $order->items->sum(fn($i) => $i->subtotal);
        $packaging = $order->packaging_cost ?? 0;
        $shipping = optional($order->location)->delivery_fee ?? 0;
        $order->amount = $amount;

        // Apply coupon (percent|fixed) if present
        $discount = 0;
        if ($order->coupon_id) {
            $order->load('coupon');
            if ($order->coupon) {
                $discountType = $order->coupon->discount_type;
                $discountValue = (float) $order->coupon->discount;
                if ($discountType === 'percent') {
                    $discount = round(($amount * $discountValue) / 100, 2);
                } else {
                    $discount = min($amount, $discountValue);
                }
            }
        }

        // Taxes
        $taxAmount = 0;
        if (config('commerce.tax.enabled')) {
            $rate = (float) config('commerce.tax.rate_percent', 0);
            $taxAmount = round((max(0, $amount - $discount) * $rate) / 100, 2);
        }

        $order->total_amount = max(0, $amount - $discount) + $packaging + $shipping + $taxAmount;
        return $order;
    }
}


