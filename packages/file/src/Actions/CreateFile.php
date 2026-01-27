<?php

namespace Lyre\File\Actions;

use Illuminate\Http\UploadedFile;

class CreateFile
{
    public static function make(array $data)
    {
        $possiblePaths = [
            storage_path('app/public/' . $data['file']),
            storage_path('app/private/' . $data['file']),
        ];

        $absolutePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $absolutePath = $path;
                break;
            }
        }

        if (!$absolutePath) {
            logger()->error("File not found in either public or private storage: {$data['file']}");
            throw new \Exception("File '{$data['file']}' not found in public or private storage");
        }

        $uploadedFile = new UploadedFile(
            $absolutePath,
            basename($absolutePath),
            mime_content_type($absolutePath),
            null,
            true
        );

        $record = fileRepository()
            ->uploadFile(
                $uploadedFile,
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['attachment_file_names'] ?? null
            );

        unlink($absolutePath);

        // TODO: Kigathi - July 15 2025 - Implement tenant association
        // if (
        //     static::getResource()::isScopedToTenant() &&
        //     ($tenant = Filament::getTenant())
        // ) {
        //     return $this->associateRecordWithTenant($record, $tenant);
        // }

        $record->save();

        return $record;
    }
}
