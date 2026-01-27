<?php

namespace Lyre\Facet\Policies;

use Lyre\Facet\Models\FacetedEntity;
use Lyre\Policy;

class FacetedEntityPolicy extends Policy
{
    public function __construct(FacetedEntity $model)
    {
        parent::__construct($model);
    }
}
