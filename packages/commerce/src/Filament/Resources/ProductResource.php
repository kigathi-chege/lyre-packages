<?php

namespace Lyre\Commerce\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lyre\Commerce\Filament\Resources\ProductResource\Pages;
use Lyre\Commerce\Filament\Resources\ProductResource\RelationManagers;
use Lyre\Facet\Filament\RelationManagers\FacetValuesRelationManager;
use Lyre\Commerce\Models\Product;
use Lyre\File\Filament\Forms\Components\SelectFromGallery;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
            SelectFromGallery::make('files')
                ->label('Product Images')
                ->multiple(),
            Forms\Components\Toggle::make('saleable'),
            Forms\Components\Section::make('HS Code Information')
                ->schema([
                    Forms\Components\TextInput::make('hscode'),
                    Forms\Components\TextInput::make('hstype'),
                    Forms\Components\Textarea::make('hsdescription')
                        ->label('HS Description'),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('featured_image.url')
                ->label('Image')
                ->circular()
                ->defaultImageUrl(url('/lyre/file/placeholder.webp')),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('saleable')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductVariantsRelationManager::class,
            FacetValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
