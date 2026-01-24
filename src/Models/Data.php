<?php

namespace Lyre\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lyre\Model;

class Data extends Model
{
    use HasFactory;

    protected $casts = [
        'filters' => 'array',
    ];

    protected array $excluded = ['created_at', 'updated_at'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
