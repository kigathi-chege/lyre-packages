<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\UserProductVariant;
use Lyre\Commerce\Repositories\Contracts\UserProductVariantRepositoryInterface;

class UserProductVariantRepository extends Repository implements UserProductVariantRepositoryInterface
{
    public function __construct(UserProductVariant $model)
    {
        parent::__construct($model);
    }
}


