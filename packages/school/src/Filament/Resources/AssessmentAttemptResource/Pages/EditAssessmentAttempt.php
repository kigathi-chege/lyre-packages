<?php

namespace Lyre\School\Filament\Resources\AssessmentAttemptResource\Pages;

use Lyre\School\Filament\Resources\AssessmentAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentAttempt extends EditRecord
{
    protected static string $resource = AssessmentAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

