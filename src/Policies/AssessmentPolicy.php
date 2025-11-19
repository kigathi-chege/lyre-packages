<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\Assessment;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;

class AssessmentPolicy extends Policy
{
    public function __construct(Assessment $model)
    {
        parent::__construct($model);
    }

    public function viewAny($user): Response
    {
        return Response::allow();
    }

    public function view($user, $model): Response
    {
        return Response::allow();
    }
}
