<?php

namespace Lyre\Facet\Filament\Resources\FacetedEntityResource\Pages;

use Lyre\Facet\Filament\Resources\FacetedEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacetedEntity extends EditRecord
{
    protected static string $resource = FacetedEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
