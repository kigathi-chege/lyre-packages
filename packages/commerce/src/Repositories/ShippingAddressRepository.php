<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\ShippingAddress;
use Lyre\Commerce\Repositories\Contracts\ShippingAddressRepositoryInterface;

class ShippingAddressRepository extends Repository implements ShippingAddressRepositoryInterface
{
    public function __construct(ShippingAddress $model)
    {
        parent::__construct($model);
    }
}


