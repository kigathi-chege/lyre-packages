<?php

namespace Lyre\File\Filament\Resources\FileResource\Pages;

use Lyre\File\Filament\Resources\FileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = \Lyre\File\Actions\CreateFile::make($data);

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            $record->associateWithTenant($tenant);
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
