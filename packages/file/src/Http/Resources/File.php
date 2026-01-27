<?php

namespace Lyre\File\Http\Resources;

use Lyre\File\Models\File as FileModel;
use Lyre\Resource;

class File extends Resource
{
    public function __construct(FileModel $model)
    {
        parent::__construct($model);
    }
}
