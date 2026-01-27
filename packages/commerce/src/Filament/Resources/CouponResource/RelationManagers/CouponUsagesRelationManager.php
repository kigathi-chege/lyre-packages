<?php

namespace Lyre\Commerce\Filament\Resources\CouponResource\RelationManagers;

use Lyre\Commerce\Filament\Resources\CouponUsageResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CouponUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('amount_saved')
                ->numeric()
                ->default(0),
            Forms\Components\DateTimePicker::make('used_at'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_at')
                    ->dateTime()
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
            ->defaultSort('used_at', 'desc');
    }
}

