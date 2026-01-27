<?php

namespace Lyre\Commerce\Http\Requests;

use Lyre\Request;

class StoreCartApplyCouponRequest extends Request
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'exists:coupons,code'],
        ];
    }
}

