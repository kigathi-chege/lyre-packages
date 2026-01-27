<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Commerce\Repositories\Contracts\ProductVariantPriceRepositoryInterface;
use Lyre\Controller;

class ProductVariantPriceController extends Controller
{
    public function __construct(ProductVariantPriceRepositoryInterface $modelRepository)
    {
        $model = new ProductVariantPrice();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


