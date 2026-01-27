<?php

namespace Lyre\Facet\Policies;

use Lyre\Facet\Models\FacetValue;
use App\Models\User;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;

class FacetValuePolicy extends Policy
{
    public function __construct(FacetValue $model)
    {
        parent::__construct($model);
    }

    public function viewAny(?User $user): Response
    {
        return Response::allow();
    }

    public function view(?User $user, $model): Response
    {
        return Response::allow();
    }
}
