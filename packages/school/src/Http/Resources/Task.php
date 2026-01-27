<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\Task as TaskModel;
use Lyre\Resource;

class Task extends Resource
{
    public function __construct(TaskModel $model)
    {
        parent::__construct($model);
    }
}

