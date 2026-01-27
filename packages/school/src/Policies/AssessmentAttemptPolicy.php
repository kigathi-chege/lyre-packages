<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\AssessmentAttempt;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;

class AssessmentAttemptPolicy extends Policy
{
    public function __construct(AssessmentAttempt $model)
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

