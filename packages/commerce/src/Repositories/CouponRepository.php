<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\Coupon;
use Lyre\Commerce\Repositories\Contracts\CouponRepositoryInterface;

class CouponRepository extends Repository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }
}


