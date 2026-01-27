<?php

namespace Lyre\School\Filament\Resources;

use Lyre\School\Filament\Resources\SelectedTaskAnswerResource\Pages;
use Lyre\School\Models\SelectedTaskAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SelectedTaskAnswerResource extends Resource
{
    protected static ?string $model = SelectedTaskAnswer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('task_id')
                    ->relationship('task', 'name')
                    ->required(),
                Forms\Components\Select::make('assessment_id')
                    ->relationship('assessment', 'name')
                    ->required(),
                Forms\Components\Select::make('assessment_task_id')
                    ->relationship('assessmentTask', 'id')
                    ->required(),
                Forms\Components\Select::make('task_answer_id')
                    ->relationship('taskAnswer', 'name')
                    ->required(),
                Forms\Components\Select::make('assessment_attempt_id')
                    ->relationship('assessmentAttempt', 'id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessment.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('taskAnswer.name')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSelectedTaskAnswers::route('/'),
            'create' => Pages\CreateSelectedTaskAnswer::route('/create'),
            'edit' => Pages\EditSelectedTaskAnswer::route('/{record}/edit'),
        ];
    }
}

