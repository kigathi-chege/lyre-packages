<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\Coupon as CouponModel;
use Lyre\Resource;

class Coupon extends Resource
{
    public function __construct(CouponModel $model)
    {
        parent::__construct($model);
    }
}


