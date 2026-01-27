<?php

namespace Lyre\File\Filament\Resources\FileResource\Pages;

use Lyre\File\Filament\Resources\FileResource;
use Lyre\File\Models\File;
use Filament\Resources\Pages\Page;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;


class Gallery extends Page
{
    protected static string $resource = FileResource::class;

    protected static string $view = 'lyre.content::filament.resources.content.file-resource.pages.gallery';

    protected function getViewData(): array
    {
        $page = request()->query('page', 1);
        \Log::info("PAGE", [$page]);

        $fileRepository = app(\Lyre\File\Repositories\Contracts\FileRepositoryInterface::class);
        $files = $fileRepository->paginate(8)->all();

        \Log::info("FILES", [$files]);

        return [
            'files' => $files,
        ];
    }
}
