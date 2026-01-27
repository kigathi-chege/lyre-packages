<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\AssessmentAttempt as AssessmentAttemptModel;
use Lyre\Resource;

class AssessmentAttempt extends Resource
{
    public function __construct(AssessmentAttemptModel $model)
    {
        parent::__construct($model);
    }
}

