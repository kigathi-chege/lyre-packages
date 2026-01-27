<?php

namespace Lyre\Commerce\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

