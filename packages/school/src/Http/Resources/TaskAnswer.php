<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\TaskAnswer as TaskAnswerModel;
use Lyre\Resource;

class TaskAnswer extends Resource
{
    public function __construct(TaskAnswerModel $model)
    {
        parent::__construct($model);
    }
}

