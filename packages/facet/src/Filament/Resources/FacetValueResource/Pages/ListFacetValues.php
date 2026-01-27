<?php

namespace Lyre\Facet\Filament\Resources\FacetValueResource\Pages;

use Lyre\Facet\Filament\Resources\FacetValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacetValues extends ListRecords
{
    protected static string $resource = FacetValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
