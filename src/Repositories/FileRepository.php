<?php

namespace Lyre\File\Repositories;

use Lyre\Exceptions\CommonException;
use Lyre\Repository;
use Lyre\File\Models\File;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;

class FileRepository extends Repository implements FileRepositoryInterface
{
    protected $model;

    public function __construct(File $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $thisModel = $this->uploadFile($data['file'], $data['name'] ?? null, $data['description'] ?? null);
        return $this->resource ? new $this->resource($thisModel) : $thisModel;
    }

    public function uploadFile($file, $name = null, $description = null, $originalName = null, $metadata = null)
    {
        $checksum = hash_file('md5', $file->getRealPath());
        $mimeType = $file->getMimeType();

        $resizedPaths = [];
        if (strpos($mimeType, 'image') !== false) {
            $resizedPaths = generate_resized_versions($file, $mimeType);
        }

        $baseName = $name ?? get_file_name_without_extension($file);
        $extension = get_file_extension($file);
        $storageDisk = config('filesystems.default');
        $directory = "uploads/{$mimeType}";

        // Ensure unique name in database - add random suffix if needed
        $storedName = $baseName;
        $counter = 0;
        while (File::where('name', $storedName)->exists()) {
            $counter++;
            $storedName = $baseName . '-' . \Illuminate\Support\Str::random(8);
        }

        // Check for storage path conflict
        while (\Illuminate\Support\Facades\Storage::disk($storageDisk)->exists("{$directory}/{$storedName}.{$extension}")) {
            $counter++;
            $storedName = $baseName . '-' . \Illuminate\Support\Str::random(8);
        }

        $filePath = $file->storeAs($directory, "{$storedName}.{$extension}", $storageDisk);

        $fileRecord = File::firstOrCreate(
            ['checksum' => $checksum],
            [
                'name' => $storedName,
                'original_name' => $originalName ?? \Lyre\File\Actions\NamesGenerator::generate(["delimiter" => "-"]),
                'path' => $filePath,
                'path_sm' => $resizedPaths['sm'] ?? null,
                'path_md' => $resizedPaths['md'] ?? null,
                'path_lg' => $resizedPaths['lg'] ?? null,
                'size' => $file->getSize(),
                'extension' => $extension,
                'mimetype' => $mimeType,
                'storage' => $storageDisk,
                'description' => $description,
                'metadata' => $metadata
            ]
        );

        if (!$fileRecord->wasRecentlyCreated) {
            $fileRecord->increment('usagecount');
        } else {
            $fileRecord->update([
                'link' => route('stream', ['slug' => $fileRecord->slug, 'extension' => $fileRecord->extension]),
            ]);
        }

        return $fileRecord;
    }

    public function delete($slug)
    {
        $file = File::where('slug', $slug)->first();

        if (! $file) {
            throw CommonException::fromMessage("File with slug {$slug} not found.");
        }

        \Lyre\File\Models\Attachment::where('file_id', $file->id)->delete();

        foreach (['path', 'path_sm', 'path_md', 'path_lg'] as $variant) {
            if ($file->$variant) {
                \Illuminate\Support\Facades\Storage::disk($file->storage)->delete($file->$variant);
            }
        }

        $file->delete();
    }
}
