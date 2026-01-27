<?php

namespace Lyre\File\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class Attachment extends Model
{
    use HasFactory;

    public function attachable()
    {
        return $this->morphTo();
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
