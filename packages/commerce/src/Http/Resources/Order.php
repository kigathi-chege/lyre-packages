<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\Order as OrderModel;
use Lyre\Resource;

class Order extends Resource
{
    public function __construct(OrderModel $model)
    {
        parent::__construct($model);
    }
}


