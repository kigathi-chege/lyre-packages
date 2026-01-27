<?php

namespace Lyre\File\Concerns;

use Lyre\File\Http\Resources\File as ResourcesFile;
use Lyre\File\Models\Attachment;
use Lyre\File\Models\File;

trait HasFile
{
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function files()
    {
        $prefix = config('lyre.table_prefix');
        return $this->hasManyThrough(File::class, Attachment::class, 'attachable_id', 'id', 'id', 'file_id')
            ->where($prefix . 'attachments.attachable_type', self::class);
    }

    public function getFeaturedImageAttribute()
    {
        $featuredImage = $this->files()->where('mimetype', 'like', 'image/%')->first();

        if ($featuredImage) {
            return ResourcesFile::make($featuredImage);
        }
    }

    /**
     * @param int[] $fileIds
     * @return Attachment[]
     *
     * This function deletes all attachments and creates new ones from fileIds
     */
    public function attachFile(array | int $fileIds)
    {
        if (!is_array($fileIds)) {
            $fileIds = [$fileIds];
        }
        $this->detachFiles();
        return $this->attachments()->createMany(array_map(fn($fileId) => ['file_id' => $fileId], $fileIds));
    }

    public function detachFiles()
    {
        $this->attachments()->delete();
    }

    public function deleteFiles()
    {
        $this->attachments()->with('file')->get()->each(function ($attachment) {
            $file = $attachment->file;

            if (!$file) {
                $attachment->delete();
                return;
            }

            $relatedAttachmentsCount = Attachment::where('file_id', $file->id)
                ->where(function ($query) {
                    $query->where('attachable_type', '!=', static::class)
                        ->orWhere('attachable_id', '!=', $this->id);
                })
                ->count();

            if ($relatedAttachmentsCount === 0) {
                fileRepository()->delete($file->slug);
            }

            $attachment->delete();
        });
    }
}
