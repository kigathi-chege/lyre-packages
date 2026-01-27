<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\OrderItem;
use Lyre\Commerce\Repositories\Contracts\OrderItemRepositoryInterface;
use Lyre\Controller;

class OrderItemController extends Controller
{
    public function __construct(OrderItemRepositoryInterface $modelRepository)
    {
        $model = new OrderItem();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


