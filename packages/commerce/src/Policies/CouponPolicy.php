<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\Coupon;
use Lyre\Policy;

class CouponPolicy extends Policy
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }
}

