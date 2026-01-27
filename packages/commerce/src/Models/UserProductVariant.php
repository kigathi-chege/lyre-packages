<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class UserProductVariant extends Model
{
    protected $fillable = [
        'user_id', 'product_variant_id', 'sku', 'stock_level', 'min_qty', 'max_qty',
    ];

    public function user()
    {
        $userClass = get_user_model();
        return $this->belongsTo($userClass);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
}


