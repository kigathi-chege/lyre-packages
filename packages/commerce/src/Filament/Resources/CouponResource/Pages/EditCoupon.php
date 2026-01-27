<?php

namespace Lyre\Commerce\Filament\Resources\CouponResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\CouponResource;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

