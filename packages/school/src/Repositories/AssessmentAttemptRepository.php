<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\Assessment;
use Lyre\School\Models\AssessmentAttempt;
use Lyre\School\Models\AssessmentTask;
use Lyre\School\Repositories\Contracts\AssessmentAttemptRepositoryInterface;
use Lyre\Exceptions\CommonException;

class AssessmentAttemptRepository extends Repository implements AssessmentAttemptRepositoryInterface
{
    protected $model;

    public function __construct(AssessmentAttempt $model)
    {
        parent::__construct($model);
    }

    public function submit(AssessmentAttempt $assessmentAttempt)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        if ($assessmentAttempt->status == 'completed') {
            throw CommonException::fromMessage("Assessment Attempt Is Already Completed", 400);
        }

        $assessmentAttempt->load('assessment');

        $assessmentAttempt->update(['status' => 'completed']);

        return $this->resource::make($assessmentAttempt);
    }
}

