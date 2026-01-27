<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\Assessment as AssessmentModel;
use Lyre\Resource;

class Assessment extends Resource
{
    public function __construct(AssessmentModel $model)
    {
        parent::__construct($model);
    }
}

