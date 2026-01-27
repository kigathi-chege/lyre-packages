<?php

namespace Lyre\School\Repositories;

use Lyre\Repository;
use Lyre\School\Models\Assessment;
use Lyre\School\Models\Task;
use Lyre\School\Repositories\Contracts\AssessmentRepositoryInterface;

class AssessmentRepository extends Repository implements AssessmentRepositoryInterface
{
    protected $model;

    public function __construct(Assessment $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $assessmentData = collect($data)->only(['name', 'description', 'published_at'])->toArray();
        $assessmentData['creator_id'] = auth()->id();

        if (array_key_exists('published', request()->query()) && request()->query('published') == 'true') {
            $assessmentData['published_at'] = now();
        }

        $assessment = $this->model->create($assessmentData);

        // Attach facet values if provided
        if (isset($data['facet_value_ids']) && is_array($data['facet_value_ids']) && !empty($data['facet_value_ids'])) {
            $assessment->attachFacetValues($data['facet_value_ids']);
        }

        foreach ($data['tasks'] as $taskData) {
            $answers = $taskData['answers'] ?? [];
            unset($taskData['answers']);

            $task = Task::create($taskData);

            foreach ($answers as $answerData) {
                $task->answers()->create($answerData);
            }

            $assessment->tasks()->attach($task->id);
        }

        return $this->resource ? new $this->resource($assessment) : $assessment;
    }

    public function update(array $data, string | int $slug, $thisModel = null)
    {
        $assessmentData = collect($data)->only(['name', 'description', 'published_at'])->toArray();

        $assessmentResource = parent::update($assessmentData, $slug, $thisModel);

        $assessment = $assessmentResource->resource;

        // Attach facet values if provided
        if (isset($data['facet_value_ids']) && is_array($data['facet_value_ids']) && !empty($data['facet_value_ids'])) {
            $assessment->attachFacetValues($data['facet_value_ids']);
        }

        $incomingTaskIds = [];

        foreach ($data['tasks'] as $taskData) {
            $answers = $taskData['answers'] ?? [];
            unset($taskData['answers']);

            if (isset($taskData['id']) || isset($taskData['slug'])) {
                $task = Task::where('id', $taskData['id'] ?? null)->orWhere('slug', $taskData['slug'] ?? null)->first();
                if ($task) {
                    $task->update($taskData);
                    $incomingTaskIds[] = $task->id;

                    // Update Task Answers
                    $existingAnswerIds = $task->answers()->pluck('id')->toArray();
                    $incomingAnswerIds = [];

                    foreach ($answers as $answerData) {
                        if (isset($answerData['id']) || isset($answerData['slug'])) {
                            $answer = $task->answers()->where('id', $answerData['id'] ?? null)->orWhere('slug', $answerData['slug'] ?? null)->first();
                            if ($answer) {
                                $answer->update($answerData);
                                $incomingAnswerIds[] = $answer->id;
                            }
                        } else {
                            $newAnswer = $task->answers()->create($answerData);
                            $incomingAnswerIds[] = $newAnswer->id;
                        }
                    }

                    $toDeleteAnswers = array_diff($existingAnswerIds, $incomingAnswerIds);
                    if (!empty($toDeleteAnswers)) {
                        $task->answers()->whereIn('id', $toDeleteAnswers)->delete();
                    }
                }
            } else {
                $task = Task::create($taskData);
                $incomingTaskIds[] = $task->id;

                // Create answers for the new task
                foreach ($answers as $answerData) {
                    $task->answers()->create($answerData);
                }
            }
        }

        // Sync the assessment's tasks
        $assessment->tasks()->sync($incomingTaskIds);

        return $assessment;
    }

    public function publish(Assessment $assessment)
    {
        $assessment->update(['published_at' => now()]);
        return new $this->resource($assessment);
    }
}

