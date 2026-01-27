<?php

namespace Lyre\Commerce\Http\Requests;

use Lyre\Request;

class StoreCheckoutConfirmShippingRequest extends Request
{
    public function rules(): array
    {
        return [
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'address_line_1' => ['required_without:shipping_address_id', 'string'],
            'delivery_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'delivery_method' => ['nullable', 'string'],
        ];
    }
}

