<?php

namespace Lyre\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class SelectedTaskAnswer extends Model
{
    use HasFactory;

    protected array $included = ['is_correct', 'score'];

    public function user()
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userClass);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function assessmentTask()
    {
        return $this->belongsTo(AssessmentTask::class);
    }

    public function taskAnswer()
    {
        return $this->belongsTo(TaskAnswer::class);
    }

    public function assessmentAttempt()
    {
        return $this->belongsTo(AssessmentAttempt::class);
    }

    public function getIsCorrectAttribute()
    {
        return $this->taskAnswer->is_correct;
    }

    public function getScoreAttribute()
    {
        return $this->taskAnswer->score;
    }
}
