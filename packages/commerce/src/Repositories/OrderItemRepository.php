<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\OrderItem;
use Lyre\Commerce\Repositories\Contracts\OrderItemRepositoryInterface;

class OrderItemRepository extends Repository implements OrderItemRepositoryInterface
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }
}


