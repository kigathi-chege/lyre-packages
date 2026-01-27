<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\Location as LocationModel;
use Lyre\Resource;

class Location extends Resource
{
    public function __construct(LocationModel $model)
    {
        parent::__construct($model);
    }
}


