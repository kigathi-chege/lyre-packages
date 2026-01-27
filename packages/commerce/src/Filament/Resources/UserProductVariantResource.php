<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\UserProductVariantResource\Pages;
use Lyre\Commerce\Filament\Resources\UserProductVariantResource\RelationManagers;
use Lyre\Commerce\Models\UserProductVariant;

class UserProductVariantResource extends Resource
{
    protected static ?string $model = UserProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('product_variant_id')
                ->relationship('productVariant', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('sku')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('stock_level')->numeric()->default(0),
            Forms\Components\TextInput::make('min_qty')->numeric(),
            Forms\Components\TextInput::make('max_qty')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('productVariant.name')->searchable(),
            Tables\Columns\TextColumn::make('sku')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('stock_level')->numeric()->sortable(),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductVariantPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserProductVariants::route('/'),
            'create' => Pages\CreateUserProductVariant::route('/create'),
            'edit' => Pages\EditUserProductVariant::route('/{record}/edit'),
        ];
    }
}

