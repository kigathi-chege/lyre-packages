<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\SelectedTaskAnswer;
use Lyre\School\Models\TaskAnswer;
use Lyre\School\Repositories\Contracts\SelectedTaskAnswerRepositoryInterface;

class SelectedTaskAnswerRepository extends Repository implements SelectedTaskAnswerRepositoryInterface
{
    protected $model;

    public function __construct(SelectedTaskAnswer $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        /**
         * NOTE: Kigathi - May 30 2025 -
         * Assumptions:
         * - A user can only have one assessment attempt at a time (at least per assessment)
         * - A task may appear in an assessment only once (This is proven)
         */

        $user = request()->user();

        $assessmentAttempt = $user->assessmentAttempts()
            ->where([
                'status' => 'in_progress',
                // 'mode' => $data['mode']
            ])
            ->whereHas('assessment', function ($query) use ($data) {
                $query->where('assessment_id', $data['assessment_id']);
            })
            ->with('selectedTaskAnswers')
            ->firstOrFail();

        // NOTE: Kigathi - June 9 2025 - Ensure that the task answer exists
        TaskAnswer::findOrFail($data['task_answer_id']);

        $data['assessment_attempt_id'] = $assessmentAttempt->id;
        $data['user_id'] = $user->id;

        $thisModel = $this->model->where('assessment_attempt_id', $assessmentAttempt->id)
            ->where('task_id', $data['task_id'])
            ->first();

        if ($thisModel) {
            $thisModel->update(['task_answer_id' => $data['task_answer_id']]);
        } else {
            $thisModel = $this->model->create($data);
        }

        $assessmentAttempt->score = $assessmentAttempt->selectedTaskAnswers()
            ->where('user_id', $user->id)
            ->with('taskAnswer')
            ->get()
            ->sum(fn($sta) => $sta->taskAnswer?->score ?? 0);
        $assessmentAttempt->save();

        return $this->resource ? new $this->resource($thisModel) : $thisModel;
    }
}
