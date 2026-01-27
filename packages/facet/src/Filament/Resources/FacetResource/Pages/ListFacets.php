<?php

namespace Lyre\Facet\Filament\Resources\FacetResource\Pages;

use Lyre\Facet\Filament\Resources\FacetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Database\Seeders\FacetHierarchyTemplates;
use Filament\Notifications\Notification;

class ListFacets extends ListRecords
{
    protected static string $resource = FacetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('apply_standard_curriculum')
                    ->label('Apply Standard Curriculum')
                    ->icon('heroicon-o-academic-cap')
                    ->tooltip('Course → Subject → Topic')
                    ->requiresConfirmation()
                    ->modalHeading('Apply Standard Curriculum Template')
                    ->modalDescription('This will create a Course → Subject → Topic hierarchy. Existing facets will not be deleted.')
                    ->action(function () {
                        $templates = new FacetHierarchyTemplates();
                        $result = $templates->applyStandardCurriculum();

                        Notification::make()
                            ->title('Standard Curriculum template applied')
                            ->success()
                            ->body(sprintf('Created hierarchy: %s', $result['description']))
                            ->send();
                    }),
                Actions\Action::make('apply_extended_curriculum')
                    ->label('Apply Extended Curriculum')
                    ->icon('heroicon-o-book-open')
                    ->tooltip('Curriculum → Learning Areas → Topics → Subtopics')
                    ->requiresConfirmation()
                    ->modalHeading('Apply Extended Curriculum Template')
                    ->modalDescription('This will create a Curriculum → Learning Areas → Topics → Subtopics hierarchy. Existing facets will not be deleted.')
                    ->action(function () {
                        $templates = new FacetHierarchyTemplates();
                        $result = $templates->applyExtendedCurriculum();

                        Notification::make()
                            ->title('Extended Curriculum template applied')
                            ->success()
                            ->body(sprintf('Created hierarchy: %s', $result['description']))
                            ->send();
                    }),
            ])
                ->label('Apply Template')
                ->icon('heroicon-o-sparkles')
                ->color('info'),
        ];
    }
}
