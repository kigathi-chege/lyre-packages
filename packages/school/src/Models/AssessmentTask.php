<?php

namespace Lyre\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class AssessmentTask extends Model
{
    use HasFactory;

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
