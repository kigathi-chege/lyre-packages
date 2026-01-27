<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\OrderItem;
use Lyre\Policy;

class OrderItemPolicy extends Policy
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }
}

