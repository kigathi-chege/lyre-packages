<?php

namespace Lyre\File\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lyre\Model;

class File extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'slug',
        "path",
        "path_sm",
        "path_md",
        "path_lg",
        "size",
        "mimetype",
        "usagecount",
        "checksum",
        "viewed_at",
        "storage",
        "laravel_through_key",
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
