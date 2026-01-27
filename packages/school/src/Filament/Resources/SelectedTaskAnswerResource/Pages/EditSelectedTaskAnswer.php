<?php

namespace Lyre\School\Filament\Resources\SelectedTaskAnswerResource\Pages;

use Lyre\School\Filament\Resources\SelectedTaskAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSelectedTaskAnswer extends EditRecord
{
    protected static string $resource = SelectedTaskAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

