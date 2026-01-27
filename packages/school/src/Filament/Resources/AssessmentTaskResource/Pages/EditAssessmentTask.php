<?php

namespace Lyre\School\Filament\Resources\AssessmentTaskResource\Pages;

use Lyre\School\Filament\Resources\AssessmentTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentTask extends EditRecord
{
    protected static string $resource = AssessmentTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

