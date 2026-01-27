<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\OrderItem as OrderItemModel;
use Lyre\Resource;

class OrderItem extends Resource
{
    public function __construct(OrderItemModel $model)
    {
        parent::__construct($model);
    }
}


