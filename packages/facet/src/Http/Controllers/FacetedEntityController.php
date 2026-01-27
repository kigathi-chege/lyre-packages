<?php

namespace Lyre\Facet\Http\Controllers;

use Lyre\Facet\Models\FacetedEntity;
use Lyre\Facet\Repositories\Contracts\FacetedEntityRepositoryInterface;
use Lyre\Controller;

class FacetedEntityController extends Controller
{
    public function __construct(
        FacetedEntityRepositoryInterface $modelRepository
    ) {
        $model = new FacetedEntity();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}
