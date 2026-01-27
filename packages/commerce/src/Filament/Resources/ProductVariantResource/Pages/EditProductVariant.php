<?php

namespace Lyre\Commerce\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\ProductVariantResource;

class EditProductVariant extends EditRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

