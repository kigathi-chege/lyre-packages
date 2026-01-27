<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\CouponUsage;
use Lyre\Commerce\Repositories\Contracts\CouponUsageRepositoryInterface;

class CouponUsageRepository extends Repository implements CouponUsageRepositoryInterface
{
    public function __construct(CouponUsage $model)
    {
        parent::__construct($model);
    }
}


