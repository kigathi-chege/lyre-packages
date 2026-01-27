<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\TaskAnswer;
use Lyre\Policy;

class TaskAnswerPolicy extends Policy
{
    public function __construct(TaskAnswer $model)
    {
        parent::__construct($model);
    }
}

