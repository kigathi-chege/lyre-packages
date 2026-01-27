<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\ProductVariantPrice as ProductVariantPriceModel;
use Lyre\Resource;

class ProductVariantPrice extends Resource
{
    public function __construct(ProductVariantPriceModel $model)
    {
        parent::__construct($model);
    }
}


