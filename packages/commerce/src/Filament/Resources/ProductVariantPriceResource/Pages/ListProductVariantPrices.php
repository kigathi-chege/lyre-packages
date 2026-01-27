<?php

namespace Lyre\Commerce\Filament\Resources\ProductVariantPriceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\ProductVariantPriceResource;

class ListProductVariantPrices extends ListRecords
{
    protected static string $resource = ProductVariantPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

