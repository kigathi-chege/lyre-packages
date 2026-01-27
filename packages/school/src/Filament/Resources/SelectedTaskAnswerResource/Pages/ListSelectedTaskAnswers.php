<?php

namespace Lyre\School\Filament\Resources\SelectedTaskAnswerResource\Pages;

use Lyre\School\Filament\Resources\SelectedTaskAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSelectedTaskAnswers extends ListRecords
{
    protected static string $resource = SelectedTaskAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

