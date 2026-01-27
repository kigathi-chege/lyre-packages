<?php

namespace Lyre\Commerce\Http\Requests;

use Lyre\Request;

class StoreCheckoutPayRequest extends Request
{
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'phone' => ['required', 'string'],
            'payment_term_reference' => ['nullable', 'string'],
        ];
    }
}

