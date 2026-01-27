<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Repositories\Contracts\ProductRepositoryInterface;
use Lyre\Controller;

class ProductController extends Controller
{
    public function __construct(ProductRepositoryInterface $modelRepository)
    {
        $model = new Product();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


