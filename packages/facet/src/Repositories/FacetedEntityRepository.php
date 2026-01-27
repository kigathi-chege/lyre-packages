<?php

namespace Lyre\Facet\Repositories;

use Lyre\Repository;
use Lyre\Facet\Models\FacetedEntity;
use Lyre\Facet\Repositories\Contracts\FacetedEntityRepositoryInterface;

class FacetedEntityRepository extends Repository implements FacetedEntityRepositoryInterface
{
    protected $model;

    public function __construct(FacetedEntity $model)
    {
        parent::__construct($model);
    }
}
