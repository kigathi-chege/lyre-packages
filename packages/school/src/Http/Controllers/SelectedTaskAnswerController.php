<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\SelectedTaskAnswer;
use Lyre\School\Repositories\Contracts\SelectedTaskAnswerRepositoryInterface;
use Lyre\Controller;

class SelectedTaskAnswerController extends Controller
{
    public function __construct(
        SelectedTaskAnswerRepositoryInterface $modelRepository
    ) {
        $model = new SelectedTaskAnswer();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}

