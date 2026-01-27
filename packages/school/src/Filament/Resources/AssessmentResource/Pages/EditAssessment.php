<?php

namespace Lyre\School\Filament\Resources\AssessmentResource\Pages;

use Lyre\School\Filament\Resources\AssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssessment extends EditRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

