<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Commerce\Repositories\Contracts\ProductVariantPriceRepositoryInterface;

class ProductVariantPriceRepository extends Repository implements ProductVariantPriceRepositoryInterface
{
    public function __construct(ProductVariantPrice $model)
    {
        parent::__construct($model);
    }
}


