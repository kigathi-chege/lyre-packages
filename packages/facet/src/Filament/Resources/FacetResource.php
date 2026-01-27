<?php

namespace Lyre\Facet\Filament\Resources;

use Closure;
use Lyre\Facet\Filament\RelationManagers\FacetValuesRelationManager;
use Lyre\Facet\Filament\Resources\FacetResource\Pages;
use Lyre\Facet\Filament\Resources\FacetResource\RelationManagers;
use Lyre\Facet\Models\Facet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FacetResource extends Resource
{
    protected static ?string $model = Facet::class;

    protected static ?string $navigationIcon = 'gmdi-category';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?int $navigationSort = 52;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('link')
                    ->maxLength(255),
                TiptapEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Facet')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Select a parent facet to create a hierarchy. Leave empty for root facets.')
                    ->reactive()
                    // ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                    //     // Additional reactive behaviors can be handled here if needed
                    // })
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                // ->rules([
                //     function (string $attribute, $value, Closure $fail) {
                //         if (! $value) {
                //             return;
                //         }

                //         $recordId = request()?->route('record');

                //         if (! $recordId) {
                //             return;
                //         }

                //         $currentFacet = Facet::find($recordId);

                //         if (! $currentFacet) {
                //             return;
                //         }

                //         if ((int) $currentFacet->id === (int) $value) {
                //             $fail('A facet cannot be its own parent.');

                //             return;
                //         }

                //         $descendants = method_exists($currentFacet, 'descendants')
                //             ? $currentFacet->descendants()
                //             : collect();

                //         if ($descendants instanceof \Illuminate\Database\Eloquent\Builder) {
                //             $descendants = $descendants->get();
                //         }

                //         if ($descendants->contains('id', (int) $value)) {
                //             $fail('Cannot set a descendant facet as parent (circular reference).');
                //         }
                //     },
                // ]),
                Forms\Components\Select::make('access')
                    ->options([
                        'public' => 'Public',
                        'private' => 'Private',
                    ])
                    ->default('public'),
            ]);
        // ->rules([
        //     'parent_id' => [
        //         function ($attribute, $value, $fail) {
        //             if ($value) {
        //                 $facet = Facet::find($value);
        //                 if ($facet && request()->route('record')) {
        //                     $currentFacet = Facet::find(request()->route('record'));
        //                     if ($currentFacet) {
        //                         // Check if the selected parent is a descendant of current facet (circular reference)
        //                         $descendants = $currentFacet->descendants();
        //                         if ($descendants->contains('id', $value)) {
        //                             $fail('Cannot set a descendant facet as parent (circular reference).');
        //                         }
        //                         // Check if trying to set itself as parent
        //                         if ($currentFacet->id == $value) {
        //                             $fail('A facet cannot be its own parent.');
        //                         }
        //                     }
        //                 }
        //             }
        //         },
        //     ],
        // ]);
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
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Children')
                    ->counts('children')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('facet_values_count')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('access')
                    ->badge()
                    ->color(
                        fn($state) => match ($state) {
                            'public' => 'success',
                            'private' => 'danger',
                        }
                    ),
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
            ->striped()
            ->deferLoading()
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            FacetValuesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacets::route('/'),
            'create' => Pages\CreateFacet::route('/create'),
            'edit' => Pages\EditFacet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if (
            static::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            static::scopeEloquentQueryToTenant($query, $tenant);
        }

        return $query->withCount(['facetValues', 'children']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        // $permissions = config('filament-shield.permission_prefixes.resource');
        // TODO: Kigathi - May 4 2025 - Users should only view this navigation if they have at least one more permission than view and viewAny
        $usingSpatieRoles = in_array(\Spatie\Permission\Traits\HasRoles::class, class_uses(\App\Models\User::class));
        return $usingSpatieRoles ? Auth::user()->can('update', new Facet) : true;
    }
}
