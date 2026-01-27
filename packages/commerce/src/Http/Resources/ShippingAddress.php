<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\ShippingAddress as ShippingAddressModel;
use Lyre\Resource;

class ShippingAddress extends Resource
{
    public function __construct(ShippingAddressModel $model)
    {
        parent::__construct($model);
    }
}


