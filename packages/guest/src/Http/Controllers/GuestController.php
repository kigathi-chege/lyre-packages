<?php

namespace Lyre\Guest\Http\Controllers;

use Lyre\Guest\Models\Guest;
use Lyre\Guest\Contracts\GuestRepositoryInterface;
use Lyre\Controller;

class GuestController extends Controller
{
    public function __construct(
        GuestRepositoryInterface $modelRepository
    ) {
        $model = new Guest();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }
}
