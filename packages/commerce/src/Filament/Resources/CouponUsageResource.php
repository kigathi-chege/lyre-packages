<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\CouponUsageResource\Pages;
use Lyre\Commerce\Models\CouponUsage;

class CouponUsageResource extends Resource
{
    protected static ?string $model = CouponUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 99;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hidden from navigation, managed via CouponResource relation manager
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('coupon_id')
                ->relationship('coupon', 'code')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('amount_saved')->numeric()->default(0),
            Forms\Components\DateTimePicker::make('used_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('coupon.code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('amount_saved')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('used_at')->dateTime()->sortable(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCouponUsages::route('/'),
            'create' => Pages\CreateCouponUsage::route('/create'),
            'edit' => Pages\EditCouponUsage::route('/{record}/edit'),
        ];
    }
}

