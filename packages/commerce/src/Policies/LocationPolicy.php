<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\Location;
use Lyre\Policy;

class LocationPolicy extends Policy
{
    public function __construct(Location $model)
    {
        parent::__construct($model);
    }
}

