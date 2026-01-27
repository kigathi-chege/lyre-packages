<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\Order;
use Lyre\Policy;

class OrderPolicy extends Policy
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }
}

