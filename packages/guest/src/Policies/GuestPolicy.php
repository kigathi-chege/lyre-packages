<?php

namespace Lyre\Guest\Policies;

use Lyre\Guest\Models\Guest;
use Lyre\Policy;

class GuestPolicy extends Policy
{
    public function __construct(Guest $model)
    {
        parent::__construct($model);
    }
}
