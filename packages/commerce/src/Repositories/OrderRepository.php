<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\Order;
use Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository extends Repository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }
}


