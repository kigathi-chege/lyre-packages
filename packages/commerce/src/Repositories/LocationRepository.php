<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\Location;
use Lyre\Commerce\Repositories\Contracts\LocationRepositoryInterface;

class LocationRepository extends Repository implements LocationRepositoryInterface
{
    public function __construct(Location $model)
    {
        parent::__construct($model);
    }
}


