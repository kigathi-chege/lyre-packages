<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\AssessmentTask;
use Lyre\School\Repositories\Contracts\AssessmentTaskRepositoryInterface;

class AssessmentTaskRepository extends Repository implements AssessmentTaskRepositoryInterface
{
    protected $model;

    public function __construct(AssessmentTask $model)
    {
        parent::__construct($model);
    }
}

