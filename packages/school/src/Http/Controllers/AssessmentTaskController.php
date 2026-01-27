<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\AssessmentTask;
use Lyre\School\Repositories\Contracts\AssessmentTaskRepositoryInterface;
use Lyre\Controller;

class AssessmentTaskController extends Controller
{
    public function __construct(
        AssessmentTaskRepositoryInterface $modelRepository
    ) {
        $model = new AssessmentTask();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}

