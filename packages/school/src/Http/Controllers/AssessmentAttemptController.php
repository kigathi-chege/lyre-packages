<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\AssessmentAttempt;
use Lyre\School\Repositories\Contracts\AssessmentAttemptRepositoryInterface;
use Lyre\Controller;

class AssessmentAttemptController extends Controller
{
    public function __construct(
        AssessmentAttemptRepositoryInterface $modelRepository
    ) {
        $model = new AssessmentAttempt();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    public function submit(AssessmentAttempt $assessmentattempt)
    {
        return $this->modelRepository->submit($assessmentattempt);
    }
}

