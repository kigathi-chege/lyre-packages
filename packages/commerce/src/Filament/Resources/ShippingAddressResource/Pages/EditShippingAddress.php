<?php

namespace Lyre\Commerce\Filament\Resources\ShippingAddressResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\ShippingAddressResource;

class EditShippingAddress extends EditRecord
{
    protected static string $resource = ShippingAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}


