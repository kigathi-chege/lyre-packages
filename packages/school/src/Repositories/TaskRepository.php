<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\Assessment;
use Lyre\School\Models\AssessmentAttempt;
use Lyre\School\Models\Task;
use Lyre\School\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository extends Repository implements TaskRepositoryInterface
{
    protected $model;

    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function update(array $data, string | int $slug, $thisModel = null)
    {
        $taskData = collect($data)->only(['name', 'description'])->toArray();

        $taskResource = parent::update($taskData, $slug, $thisModel);

        $task = $taskResource->resource;

        $existingAnswerIds = $task->answers()->pluck('id')->toArray();
        $incomingAnswerIds = [];

        if (isset($data['answers']) && !empty($data['answers'])) {
            foreach ($data['answers'] as $answerData) {
                if (isset($answerData['id'])) {
                    $answer = $task->answers()->where('id', $answerData['id'])->first();
                    if ($answer) {
                        $answer->update($answerData);
                        $incomingAnswerIds[] = $answer->id;
                    }
                } else {
                    $newAnswer = $task->answers()->create($answerData);
                    $incomingAnswerIds[] = $newAnswer->id;
                }
            }
        }

        $toDelete = array_diff($existingAnswerIds, $incomingAnswerIds);
        if (!empty($toDelete)) {
            $task->answers()->whereIn('id', $toDelete)->delete();
        }

        return $task;
    }

    public function delete($slug)
    {
        $taskSlug = $slug;
        $assessmentSlug = null;

        if (str_contains($slug, ',assessment:')) {
            [$taskSlug, $assessmentPart] = explode(',assessment:', $slug, 2);
            $assessmentSlug = trim($assessmentPart);
        }

        $task = Task::where('slug', $taskSlug)->firstOrFail();

        if ($assessmentSlug) {
            $assessment = Assessment::where('slug', $assessmentSlug)->firstOrFail();
            $assessment->tasks()->detach($task->id);
        } else {
            $task->delete();
        }

        return $this->resource ? new $this->resource($task) : $task;
    }
}

