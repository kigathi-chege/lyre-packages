<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id', 'user_id', 'amount_saved', 'used_at', 'entity_type', 'entity_id', 'transaction_id',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        $userClass = get_user_model();
        return $this->belongsTo($userClass);
    }
}


