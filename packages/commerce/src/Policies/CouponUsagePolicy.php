<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\CouponUsage;
use Lyre\Policy;

class CouponUsagePolicy extends Policy
{
    public function __construct(CouponUsage $model)
    {
        parent::__construct($model);
    }
}

