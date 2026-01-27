<?php

namespace Lyre\File\Policies;

use Lyre\File\Models\Attachment;
use Lyre\Policy;

class AttachmentPolicy extends Policy
{
    public function __construct(Attachment $model)
    {
        parent::__construct($model);
    }
}
