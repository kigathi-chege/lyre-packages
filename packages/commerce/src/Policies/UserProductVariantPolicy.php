<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\UserProductVariant;
use App\Models\User;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;

class UserProductVariantPolicy extends Policy
{
    public function __construct(UserProductVariant $model)
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
