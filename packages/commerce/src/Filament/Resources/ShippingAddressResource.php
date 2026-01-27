<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\ShippingAddressResource\Pages;
use Lyre\Commerce\Models\ShippingAddress;

class ShippingAddressResource extends Resource
{
    protected static ?string $model = ShippingAddress::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_id')->required(),
            Forms\Components\TextInput::make('location_id'),
            Forms\Components\TextInput::make('delivery_method'),
            Forms\Components\TextInput::make('address_line_1'),
            Forms\Components\TextInput::make('city'),
            Forms\Components\TextInput::make('country'),
            Forms\Components\Toggle::make('is_default'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user_id')->sortable(),
            Tables\Columns\TextColumn::make('delivery_method'),
            Tables\Columns\TextColumn::make('address_line_1')->limit(30),
            Tables\Columns\IconColumn::make('is_default')->boolean(),
        ])
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
            'index' => Pages\ListShippingAddresses::route('/'),
            'create' => Pages\CreateShippingAddress::route('/create'),
            'edit' => Pages\EditShippingAddress::route('/{record}/edit'),
        ];
    }
}


