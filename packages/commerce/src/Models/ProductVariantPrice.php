<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'user_product_variant_id', 'price', 'currency', 'compare_at_price', 'tax_included', 'effective_from', 'effective_through',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_through' => 'datetime',
        'tax_included' => 'boolean',
    ];

    public function userProductVariant()
    {
        return $this->belongsTo(UserProductVariant::class);
    }
}


