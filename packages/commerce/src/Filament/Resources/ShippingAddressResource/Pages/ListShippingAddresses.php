<?php

namespace Lyre\Commerce\Filament\Resources\ShippingAddressResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\ShippingAddressResource;

class ListShippingAddresses extends ListRecords
{
    protected static string $resource = ShippingAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}


