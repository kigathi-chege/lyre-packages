<?php

namespace Lyre\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Facet\Concerns\HasFacet;
use Lyre\Model;
use Lyre\School\Http\Resources\SelectedTaskAnswer as SelectedTaskAnswerResource;
use Lyre\School\Models\Assessment as AssessmentModel;
use Lyre\School\Models\AssessmentAttempt;

class Task extends Model
{
    use HasFactory, HasFacet;

    const ID_COLUMN = 'slug';

    protected $guarded = ['id'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    protected array $included = ['selected_answer'];

    public function answers()
    {
        return $this->hasMany(TaskAnswer::class, 'task_id', 'id')
            ->inRandomOrder();
    }

    public function assessments()
    {
        $prefix = config('lyre.table_prefix');
        return $this->belongsToMany(Assessment::class, $prefix . 'assessment_tasks', 'task_id', 'assessment_id')
            ->withTimestamps();
    }

    public function selectedTaskAnswers()
    {
        return $this->hasMany(SelectedTaskAnswer::class);
    }

    public function getSelectedAnswerAttribute()
    {
        $user = request()->user();

        if (! $user) {
            return null;
        }

        $attemptId = request()->query('attempt');

        if ($attemptId) {
            $assessmentAttempt = AssessmentAttempt::find($attemptId);
        } else {
            // $mode = request()->query('mode', 'practice');
            $relation = request()->query('relation', '');
            $parts = array_values(array_filter(explode(',', $relation)));

            $assessmentAttempt = null;
            $assessmentKey = array_search('assessments', $parts);

            if ($assessmentKey !== false && isset($parts[$assessmentKey + 1])) {
                $assessmentSlug = $parts[$assessmentKey + 1];
                $assessment = AssessmentModel::where('slug', $assessmentSlug)->first();

                $assessmentAttempt = $user->assessmentAttempts()
                    ->where('status', 'in_progress')
                    // ->where('mode', $mode)
                    ->where('assessment_id', $assessment?->id)
                    ->first();
            }

            if (! $assessmentAttempt) {
                $assessmentAttempt = $user->assessmentAttempts()
                    ->where('status', 'in_progress')
                    // ->where('mode', $mode)
                    ->latest()
                    ->first();
            }
        }

        if (! $assessmentAttempt) {
            return null;
        }

        $selectedAnswer = $this->selectedTaskAnswers()
            ->where('assessment_attempt_id', $assessmentAttempt->id)
            ->first();

        return $selectedAnswer ? SelectedTaskAnswerResource::make($selectedAnswer) : null;
    }
}
