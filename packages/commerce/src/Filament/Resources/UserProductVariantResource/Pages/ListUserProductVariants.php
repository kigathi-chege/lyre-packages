<?php

namespace Lyre\Commerce\Filament\Resources\UserProductVariantResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\UserProductVariantResource;

class ListUserProductVariants extends ListRecords
{
    protected static string $resource = UserProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

