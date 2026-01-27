<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\TaskAnswer;
use Lyre\School\Repositories\Contracts\TaskAnswerRepositoryInterface;

class TaskAnswerRepository extends Repository implements TaskAnswerRepositoryInterface
{
    protected $model;

    public function __construct(TaskAnswer $model)
    {
        parent::__construct($model);
    }
}

