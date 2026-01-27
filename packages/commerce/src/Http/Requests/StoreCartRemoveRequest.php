<?php

namespace Lyre\Commerce\Http\Requests;

use Lyre\Commerce\Models\ProductVariant;
use Lyre\Request;
use Lyre\Rules\IsId;

class StoreCartRemoveRequest extends Request
{
    public function rules(): array
    {
        $prefix = config('lyre.table_prefix');
        return [
            'product_variant_id' => ['required', IsId::make(ProductVariant::class)],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
