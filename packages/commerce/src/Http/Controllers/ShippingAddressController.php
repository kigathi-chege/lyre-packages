<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\ShippingAddress;
use Lyre\Commerce\Repositories\Contracts\ShippingAddressRepositoryInterface;
use Lyre\Controller;

class ShippingAddressController extends Controller
{
    public function __construct(ShippingAddressRepositoryInterface $modelRepository)
    {
        $model = new ShippingAddress();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


