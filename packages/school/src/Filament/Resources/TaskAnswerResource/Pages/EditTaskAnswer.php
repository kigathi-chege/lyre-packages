<?php

namespace Lyre\School\Filament\Resources\TaskAnswerResource\Pages;

use Lyre\School\Filament\Resources\TaskAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskAnswer extends EditRecord
{
    protected static string $resource = TaskAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

