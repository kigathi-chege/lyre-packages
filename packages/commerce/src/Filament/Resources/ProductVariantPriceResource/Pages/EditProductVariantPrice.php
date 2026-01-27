<?php

namespace Lyre\Commerce\Filament\Resources\ProductVariantPriceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\ProductVariantPriceResource;

class EditProductVariantPrice extends EditRecord
{
    protected static string $resource = ProductVariantPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

