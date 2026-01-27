<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\UserProductVariant;
use Lyre\Commerce\Repositories\Contracts\UserProductVariantRepositoryInterface;
use Lyre\Controller;

class UserProductVariantController extends Controller
{
    public function __construct(UserProductVariantRepositoryInterface $modelRepository)
    {
        $model = new UserProductVariant();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


