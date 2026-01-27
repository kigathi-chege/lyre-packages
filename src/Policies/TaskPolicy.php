<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\Task;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;
use App\Models\User;

class TaskPolicy extends Policy
{
    public function __construct(Task $model)
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
