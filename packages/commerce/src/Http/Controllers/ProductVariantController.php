<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\ProductVariant;
use Lyre\Commerce\Repositories\Contracts\ProductVariantRepositoryInterface;
use Lyre\Controller;

class ProductVariantController extends Controller
{
    public function __construct(ProductVariantRepositoryInterface $modelRepository)
    {
        $model = new ProductVariant();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


