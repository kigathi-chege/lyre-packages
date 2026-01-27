<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\Coupon;
use Lyre\Commerce\Repositories\Contracts\CouponRepositoryInterface;
use Lyre\Controller;

class CouponController extends Controller
{
    public function __construct(CouponRepositoryInterface $modelRepository)
    {
        $model = new Coupon();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


