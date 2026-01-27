<?php

namespace Lyre\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $with = ['assessment'];

    protected $casts = [
        'task_order' => 'array'
    ];

    protected array $included = [
        'progress',
        'total_score',
        'current_score',
        'total_tasks',
        'correct_answers',
        'total_attempts'
    ];

    public function user()
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userClass);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function selectedTaskAnswers()
    {
        return $this->hasMany(SelectedTaskAnswer::class);
    }

    public function getProgressAttribute()
    {
        $selectedTaskAnswersCount = $this->selectedTaskAnswers()->count();
        if ($selectedTaskAnswersCount == 0) return 0;
        $totalTasks = optional($this->assessment)->tasks()->count() ?? 0;
        if ($totalTasks === 0) return 0;
        return ($selectedTaskAnswersCount / $totalTasks) * 100;
    }

    public function getTotalScoreAttribute()
    {
        return optional($this->assessment)->tasks()
            ->with('answers')
            ->get()
            ->sum(fn($sta) => $sta->answers()->sum('score'));
    }

    public function getCurrentScoreAttribute()
    {
        return $this->selectedTaskAnswers()
            ->where('user_id', auth()->id())
            ->with('taskAnswer')
            ->get()
            ->sum(fn($sta) => $sta->taskAnswer->score ?? 0);
    }

    public function getTotalTasksAttribute()
    {
        return optional($this->assessment)->tasks()->count();
    }

    public function getCorrectAnswersAttribute()
    {
        return $this->selectedTaskAnswers()->whereHas('taskAnswer', fn($query) => $query->where('is_correct', true))->count();
    }

    public function getTotalAttemptsAttribute()
    {
        return $this->assessment->currentAttempts()->count();
    }
}
