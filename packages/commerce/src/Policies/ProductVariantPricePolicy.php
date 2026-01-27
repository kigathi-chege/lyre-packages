<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Policy;

class ProductVariantPricePolicy extends Policy
{
    public function __construct(ProductVariantPrice $model)
    {
        parent::__construct($model);
    }
}

