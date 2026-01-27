<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\TaskAnswer;
use Lyre\School\Repositories\Contracts\TaskAnswerRepositoryInterface;
use Lyre\Controller;

class TaskAnswerController extends Controller
{
    public function __construct(
        TaskAnswerRepositoryInterface $modelRepository
    ) {
        $model = new TaskAnswer();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}

