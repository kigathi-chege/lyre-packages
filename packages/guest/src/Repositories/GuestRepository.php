<?php

namespace Lyre\Guest\Repositories;

use Lyre\Repository;
use Lyre\Guest\Models\Guest;
use Lyre\Guest\Contracts\GuestRepositoryInterface;

class GuestRepository extends Repository implements GuestRepositoryInterface
{
    protected $model;

    public function __construct(Guest $model)
    {
        parent::__construct($model);
    }
}
