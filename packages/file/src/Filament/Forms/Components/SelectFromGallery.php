<?php

namespace Lyre\File\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class SelectFromGallery extends Field
{
    protected bool | Closure $multiple = false;
    protected string $relationship;
    protected array $galleryFiles = [];
    protected array $selectedFiles = [];
    protected int $galleryPage = 1;
    protected int $perPage = 8;

    protected string $view = 'lyre.file::forms.components.select-from-gallery';

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (SelectFromGallery $component, $state) {
            $record = $component->getRecord();

            if ($record) {
                $component->selectedFiles = $record->files->toArray();
            }
        });

        $this->saveRelationshipsUsing(static function ($component, $record, $state) {
            if (!empty($state)) {
                $record->attachFile($state);
            }
        });

        $this->dehydrated(false);
    }

    public function multiple(bool| Closure  $condition = true): static
    {
        $this->multiple = $condition;
        return $this;
    }

    public function getMultiple(): ?bool
    {
        return $this->evaluate($this->multiple);
    }

    public function selectedFiles($files): static
    {
        $this->selectedFiles = $files;

        return $this;
    }

    public function getSelectedFiles(): ?array
    {
        return $this->evaluate($this->selectedFiles);
    }
}
