<?php

namespace Lyre\File\Http\Resources;

use Lyre\File\Models\Attachment as AttachmentModel;
use Lyre\Resource;

class Attachment extends Resource
{
    public function __construct(AttachmentModel $model)
    {
        parent::__construct($model);
    }
}
