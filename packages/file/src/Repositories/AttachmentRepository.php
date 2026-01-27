<?php

namespace Lyre\File\Repositories;

use Lyre\Repository;
use Lyre\File\Models\Attachment;
use Lyre\File\Repositories\Contracts\AttachmentRepositoryInterface;

class AttachmentRepository extends Repository implements AttachmentRepositoryInterface
{
    protected $model;

    public function __construct(Attachment $model)
    {
        parent::__construct($model);
    }
}
