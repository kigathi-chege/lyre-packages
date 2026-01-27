<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\SelectedTaskAnswer;
use Lyre\Policy;

class SelectedTaskAnswerPolicy extends Policy
{
    public function __construct(SelectedTaskAnswer $model)
    {
        parent::__construct($model);
    }
}

