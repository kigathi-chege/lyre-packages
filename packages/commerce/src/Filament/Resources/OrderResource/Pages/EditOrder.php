<?php

namespace Lyre\Commerce\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

