<?php

namespace Lyre\Guest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class Guest extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(get_user_model());
    }
}
