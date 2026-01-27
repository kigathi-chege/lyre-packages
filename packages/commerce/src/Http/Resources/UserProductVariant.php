<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\UserProductVariant as UserProductVariantModel;
use Lyre\Resource;

class UserProductVariant extends Resource
{
    public function __construct(UserProductVariantModel $model)
    {
        parent::__construct($model);
    }
}


