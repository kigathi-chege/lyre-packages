<?php

namespace Lyre\Commerce\Policies;

use Lyre\Commerce\Models\ProductVariant;
use App\Models\User;
use Lyre\Policy;
use Illuminate\Auth\Access\Response;

class ProductVariantPolicy extends Policy
{
    public function __construct(ProductVariant $model)
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
