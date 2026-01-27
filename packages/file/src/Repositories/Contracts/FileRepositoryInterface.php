<?php

namespace Lyre\File\Repositories\Contracts;

use Lyre\Interface\RepositoryInterface;

interface FileRepositoryInterface extends RepositoryInterface
{
    public function uploadFile($file, $name = null, $description = null);
}
