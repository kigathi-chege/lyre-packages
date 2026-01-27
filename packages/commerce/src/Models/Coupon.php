<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'discount', 'discount_type', 'start_date', 'end_date', 'status', 'usage_limit', 'used_count', 'minimum_amount', 'applies_to',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'applies_to' => 'array',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}


