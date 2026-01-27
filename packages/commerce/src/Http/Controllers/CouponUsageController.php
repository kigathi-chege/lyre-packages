<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\CouponUsage;
use Lyre\Commerce\Repositories\Contracts\CouponUsageRepositoryInterface;
use Lyre\Controller;

class CouponUsageController extends Controller
{
    public function __construct(CouponUsageRepositoryInterface $modelRepository)
    {
        $model = new CouponUsage();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


