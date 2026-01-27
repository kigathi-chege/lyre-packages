<?php

namespace Lyre\Facet\Filament\Resources\FacetValueResource\Pages;

use Lyre\Facet\Filament\Resources\FacetValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacetValue extends EditRecord
{
    protected static string $resource = FacetValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
