<?php

namespace Lyre\Commerce\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\ProductVariantResource;

class ListProductVariants extends ListRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

