<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\ProductVariantPriceResource\Pages;
use Lyre\Commerce\Models\ProductVariantPrice;

class ProductVariantPriceResource extends Resource
{
    protected static ?string $model = ProductVariantPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_product_variant_id')
                ->relationship('userProductVariant', 'sku')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->default('USD')->required(),
            Forms\Components\TextInput::make('compare_at_price')->numeric(),
            Forms\Components\Toggle::make('tax_included')->default(false),
            Forms\Components\DateTimePicker::make('effective_from'),
            Forms\Components\DateTimePicker::make('effective_through'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('userProductVariant.sku')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('price')->money('currency')->sortable(),
            Tables\Columns\TextColumn::make('currency')->sortable(),
            Tables\Columns\IconColumn::make('tax_included')->boolean(),
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
            'index' => Pages\ListProductVariantPrices::route('/'),
            'create' => Pages\CreateProductVariantPrice::route('/create'),
            'edit' => Pages\EditProductVariantPrice::route('/{record}/edit'),
        ];
    }
}

