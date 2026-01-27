<?php

namespace Lyre\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Facet\Concerns\HasFacet;
use Lyre\Model;

class Assessment extends Model
{
    use HasFactory, HasFacet;

    const ID_COLUMN = 'slug';

    protected $guarded = ['id'];

    protected array $included = ['progress', 'total_score', 'current_score', 'current_status', 'published_at'];

    public function tasks()
    {
        $prefix = config('lyre.table_prefix');
        return $this->belongsToMany(Task::class, $prefix . 'assessment_tasks', 'assessment_id', 'task_id')
            ->withTimestamps();
    }

    public function selectedTaskAnswers()
    {
        return $this->hasMany(SelectedTaskAnswer::class);
    }

    public function creator()
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userClass, 'creator_id');
    }

    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class, 'assessment_id');
    }

    public function currentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class, 'assessment_id')->where('user_id', auth()->id())->latest();
    }

    public function getProgressAttribute()
    {
        $latestAttempt = $this->assessmentAttempts()
            ->where('user_id', auth()->id())
            ->latest()
            ->withCount('selectedTaskAnswers')
            ->first();

        $selectedTaskAnswersCount = $latestAttempt?->selected_task_answers_count ?? 0;
        $totalTasksCount = $this->tasks()->count();

        if ($selectedTaskAnswersCount === 0 || $totalTasksCount === 0) {
            return 0;
        }

        return ($selectedTaskAnswersCount / $totalTasksCount) * 100;
    }

    public function getTotalScoreAttribute()
    {
        return $this->tasks()
            ->with('answers')
            ->get()
            ->sum(fn($sta) => $sta->answers()->sum('score'));
    }

    public function getCurrentScoreAttribute()
    {
        $latestAttempt = $this->assessmentAttempts()
            ->where('user_id', auth()->id())
            ->latest()
            ->with('selectedTaskAnswers.taskAnswer')
            ->first();

        return $latestAttempt?->selectedTaskAnswers->sum(fn($sta) => $sta->taskAnswer->score ?? 0) ?? 0;
    }
}
