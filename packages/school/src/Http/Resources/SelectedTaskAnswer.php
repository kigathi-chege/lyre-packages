<?php

namespace Lyre\School\Http\Resources;

use Lyre\School\Models\SelectedTaskAnswer as SelectedTaskAnswerModel;
use Lyre\Resource;

class SelectedTaskAnswer extends Resource
{
    public function __construct(SelectedTaskAnswerModel $model)
    {
        parent::__construct($model);
    }
}

