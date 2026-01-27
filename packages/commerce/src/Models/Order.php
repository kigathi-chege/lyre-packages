<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'amount', 'total_amount', 'customer_id', 'payment_term_id', 'packaging_cost', 'status', 'shipping_address_id', 'location_id', 'coupon_id', 'notes', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function customer()
    {
        $userClass = get_user_model();
        return $this->belongsTo($userClass, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function transactions()
    {
        if (class_exists(\Lyre\Billing\Models\Transaction::class)) {
            return $this->hasMany(\Lyre\Billing\Models\Transaction::class, 'order_reference', 'reference');
        }
        return null;
    }
}


