<?php

namespace Lyre\Commerce\Services;

use Lyre\Commerce\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Support\Carbon;

class CouponService
{
    public function __construct(protected CouponRepositoryInterface $coupons) {}

    public function validateAndResolve(string $code)
    {
        $coupon = $this->coupons
            ->columnFilters(['code' => $code])
            ->first()
            ->resource ?? null;

        if (!$coupon) {
            throw new \Exception('Invalid coupon code');
        }

        $now = Carbon::now();
        if (($coupon->start_date && $now->lt($coupon->start_date)) || ($coupon->end_date && $now->gt($coupon->end_date))) {
            throw new \Exception('Coupon not active');
        }

        if ($coupon->status !== 'active') {
            throw new \Exception('Coupon inactive');
        }

        if (!is_null($coupon->usage_limit) && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            throw new \Exception('Coupon usage limit reached');
        }

        return $coupon;
    }
}


