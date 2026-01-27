<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\Order;
use Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface;
use Lyre\Controller;

class OrderController extends Controller
{
    public function __construct(OrderRepositoryInterface $modelRepository)
    {
        $model = new Order();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


