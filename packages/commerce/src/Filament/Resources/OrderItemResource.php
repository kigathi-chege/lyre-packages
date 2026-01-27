<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\OrderItemResource\Pages;
use Lyre\Commerce\Models\OrderItem;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 99;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hidden from navigation, managed via OrderResource relation manager
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('order_id')
                ->relationship('order', 'reference')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('product_variant_id')
                ->relationship('productVariant', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('unit_price')->numeric()->required(),
            Forms\Components\TextInput::make('quantity')->numeric()->required()->default(1),
            Forms\Components\TextInput::make('subtotal')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->default('USD'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('order.reference')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('productVariant.name')->searchable(),
            Tables\Columns\TextColumn::make('quantity')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('unit_price')->money('currency')->sortable(),
            Tables\Columns\TextColumn::make('subtotal')->money('currency')->sortable(),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}

