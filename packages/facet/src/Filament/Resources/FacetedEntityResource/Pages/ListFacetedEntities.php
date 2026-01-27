<?php

namespace Lyre\Facet\Filament\Resources\FacetedEntityResource\Pages;

use Lyre\Facet\Filament\Resources\FacetedEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacetedEntities extends ListRecords
{
    protected static string $resource = FacetedEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
