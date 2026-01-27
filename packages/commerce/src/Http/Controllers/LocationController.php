<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Models\Location;
use Lyre\Commerce\Repositories\Contracts\LocationRepositoryInterface;
use Lyre\Controller;

class LocationController extends Controller
{
    public function __construct(LocationRepositoryInterface $modelRepository)
    {
        $model = new Location();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}


