<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\CouponUsage as CouponUsageModel;
use Lyre\Resource;

class CouponUsage extends Resource
{
    public function __construct(CouponUsageModel $model)
    {
        parent::__construct($model);
    }
}


