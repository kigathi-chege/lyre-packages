<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\ShippingAddress;
use Lyre\Policy;

class ShippingAddressPolicy extends Policy
{
    public function __construct(ShippingAddress $model)
    {
        parent::__construct($model);
    }
}

