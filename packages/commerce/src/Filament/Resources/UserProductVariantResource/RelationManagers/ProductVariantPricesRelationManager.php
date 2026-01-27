<?php

namespace Lyre\Commerce\Filament\Resources\UserProductVariantResource\RelationManagers;

use Lyre\Commerce\Filament\Resources\ProductVariantPriceResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->default('USD')->required(),
            Forms\Components\TextInput::make('compare_at_price')->numeric(),
            Forms\Components\Toggle::make('tax_included')->default(false),
            Forms\Components\DateTimePicker::make('effective_from'),
            Forms\Components\DateTimePicker::make('effective_through'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                Tables\Columns\TextColumn::make('price')->money('currency')->sortable(),
                Tables\Columns\TextColumn::make('currency')->sortable(),
                Tables\Columns\IconColumn::make('tax_included')->boolean(),
                Tables\Columns\TextColumn::make('effective_from')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('effective_through')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->striped()
            ->deferLoading()
            ->defaultSort('created_at', 'desc');
    }
}

