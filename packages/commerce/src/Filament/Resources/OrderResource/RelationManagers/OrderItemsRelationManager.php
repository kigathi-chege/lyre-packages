<?php

namespace Lyre\Commerce\Filament\Resources\OrderResource\RelationManagers;

use Lyre\Commerce\Filament\Resources\OrderItemResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_variant_id')
                ->relationship('productVariant', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('unit_price')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1),
            Forms\Components\TextInput::make('subtotal')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('currency')
                ->default('USD'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('productVariant.name')
            ->columns([
                Tables\Columns\TextColumn::make('productVariant.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('currency')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('currency')
                    ->sortable(),
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

