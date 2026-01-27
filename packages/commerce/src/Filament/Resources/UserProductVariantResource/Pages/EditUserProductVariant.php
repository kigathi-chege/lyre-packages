<?php

namespace Lyre\Commerce\Filament\Resources\UserProductVariantResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\UserProductVariantResource;

class EditUserProductVariant extends EditRecord
{
    protected static string $resource = UserProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

