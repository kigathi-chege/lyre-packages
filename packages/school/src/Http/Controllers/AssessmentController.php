<?php

namespace Lyre\School\Http\Controllers;

use Lyre\School\Models\Assessment;
use Lyre\School\Repositories\Contracts\AssessmentRepositoryInterface;
use Lyre\Controller;

class AssessmentController extends Controller
{
    public function __construct(
        AssessmentRepositoryInterface $modelRepository
    ) {
        $model = new Assessment();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    public function publish(string $assessment)
    {
        $assessment = $this->modelRepository->find(['slug' => $assessment])?->resource;
        return $this->modelRepository->publish($assessment);
    }
}
