<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\Task;
use Lyre\School\Repositories\Contracts\TaskRepositoryInterface;
use Lyre\Controller;

class TaskController extends Controller
{
    public function __construct(
        TaskRepositoryInterface $modelRepository
    ) {
        $model = new Task();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}

