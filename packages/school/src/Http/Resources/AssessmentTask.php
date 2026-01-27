<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\AssessmentTask as AssessmentTaskModel;
use Lyre\Resource;

class AssessmentTask extends Resource
{
    public function __construct(AssessmentTaskModel $model)
    {
        parent::__construct($model);
    }
}

