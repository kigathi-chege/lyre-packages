<?php

namespace Lyre\Commerce\Filament\Resources\CouponUsageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lyre\Commerce\Filament\Resources\CouponUsageResource;

class EditCouponUsage extends EditRecord
{
    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

