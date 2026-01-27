<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'unit_price', 'quantity', 'subtotal', 'currency', 'snapshot',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}


