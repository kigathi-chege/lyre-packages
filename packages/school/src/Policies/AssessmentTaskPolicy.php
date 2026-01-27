<?php

namespace Lyre\School\Policies;

use Lyre\School\Models\AssessmentTask;
use Lyre\Policy;

class AssessmentTaskPolicy extends Policy
{
    public function __construct(AssessmentTask $model)
    {
        parent::__construct($model);
    }
}

