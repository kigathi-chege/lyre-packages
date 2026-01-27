<?php

namespace Lyre\File\Policies;

use Lyre\File\Models\File;
use Lyre\Policy;

class FilePolicy extends Policy
{
    public function __construct(File $model)
    {
        parent::__construct($model);
    }
}
