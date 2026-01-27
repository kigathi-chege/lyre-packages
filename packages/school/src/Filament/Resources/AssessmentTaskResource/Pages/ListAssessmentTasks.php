<?php

namespace Lyre\School\Filament\Resources\AssessmentTaskResource\Pages;

use Lyre\School\Filament\Resources\AssessmentTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentTasks extends ListRecords
{
    protected static string $resource = AssessmentTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

