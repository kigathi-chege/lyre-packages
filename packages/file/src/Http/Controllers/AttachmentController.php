<?php

namespace Lyre\File\Http\Controllers;

use Lyre\File\Models\Attachment;
use Lyre\File\Repositories\Contracts\AttachmentRepositoryInterface;
use Lyre\Controller;

class AttachmentController extends Controller
{
    public function __construct(
        AttachmentRepositoryInterface $modelRepository
    ) {
        $model = new Attachment();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}
