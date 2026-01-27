<?php

namespace Lyre\School\Filament\Resources;

use Lyre\School\Filament\Resources\AssessmentTaskResource\Pages;
use Lyre\School\Models\AssessmentTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssessmentTaskResource extends Resource
{
    protected static ?string $model = AssessmentTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('assessment_id')
                    ->relationship('assessment', 'name')
                    ->required(),
                Forms\Components\Select::make('task_id')
                    ->relationship('task', 'name')
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
                Tables\Columns\TextColumn::make('assessment.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task.name')
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
            'index' => Pages\ListAssessmentTasks::route('/'),
            'create' => Pages\CreateAssessmentTask::route('/create'),
            'edit' => Pages\EditAssessmentTask::route('/{record}/edit'),
        ];
    }
}

