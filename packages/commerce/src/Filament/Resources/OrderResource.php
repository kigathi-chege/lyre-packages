<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\OrderResource\Pages;
use Lyre\Commerce\Filament\Resources\OrderResource\RelationManagers;
use Lyre\Commerce\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reference')->unique(ignoreRecord: true),
            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('amount')->numeric(),
            Forms\Components\TextInput::make('total_amount')->numeric(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'invoiced' => 'Invoiced',
                    'paid' => 'Paid',
                    'ready_for_fulfillment' => 'Ready for Fulfillment',
                    'fulfilled' => 'Fulfilled',
                    'canceled' => 'Canceled',
                ])
                ->default('pending'),
            Forms\Components\TextInput::make('packaging_cost')->numeric(),
            Forms\Components\Select::make('shipping_address_id')
                ->relationship('shippingAddress', 'address_line_1')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('location_id')
                ->relationship('location', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('coupon_id')
                ->relationship('coupon', 'code')
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('total_amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->colors([
                    'warning' => 'pending',
                    'info' => 'confirmed',
                    'primary' => 'invoiced',
                    'success' => 'paid',
                    'success' => 'ready_for_fulfillment',
                    'success' => 'fulfilled',
                    'danger' => 'canceled',
                ]),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'invoiced' => 'Invoiced',
                    'paid' => 'Paid',
                    'ready_for_fulfillment' => 'Ready for Fulfillment',
                    'fulfilled' => 'Fulfilled',
                    'canceled' => 'Canceled',
                ]),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->deferLoading()
            ->defaultSort(function () {
                $prefix = config('lyre.table_prefix');
                return "{$prefix}orders.created_at";
            }, 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
