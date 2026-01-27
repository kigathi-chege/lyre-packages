<?php

namespace Lyre\Commerce\Http\Requests;

use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Models\ProductVariant;
use Lyre\Request;
use Lyre\Rules\IsId;

class StoreCartAddRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_variant_id' => ['nullable', IsId::make(ProductVariant::class)],
            'product_id' => ['nullable', IsId::make(Product::class)],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
