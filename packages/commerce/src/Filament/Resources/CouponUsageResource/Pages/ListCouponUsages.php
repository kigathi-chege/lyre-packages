<?php

namespace Lyre\Commerce\Filament\Resources\CouponUsageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lyre\Commerce\Filament\Resources\CouponUsageResource;

class ListCouponUsages extends ListRecords
{
    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

