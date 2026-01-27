<?php

namespace Lyre\School\Filament\Resources;

use Lyre\School\Filament\Resources\TaskResource\Pages;
use Lyre\School\Filament\Resources\TaskResource\RelationManagers;
use Lyre\School\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Auth;
use Lyre\Facet\Filament\RelationManagers\FacetValuesRelationManager;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'School';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TiptapEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('categories')
                    ->label('Categories')
                    ->relationship(
                        name: 'facetValues',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn() => \Lyre\Facet\Models\FacetValue::query(),
                    )
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->facet_name})")
                    ->saveRelationshipsUsing(static function ($component, $record, $state) {
                        if (!empty($state)) {
                            $record->attachFacetValues($state);
                        }
                    })
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TagsColumn::make('facetValues.name')
                    ->label('Categories')
                    ->limit(3),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnswersRelationManager::class,
            FacetValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        return Auth::user()->can('update', new Task);
    }
}

