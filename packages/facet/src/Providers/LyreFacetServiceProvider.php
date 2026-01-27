<?php

namespace Lyre\Facet\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lyre\Facet\Filament\RelationManagers\FacetValuesRelationManager;

class LyreFacetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_repositories($this->app, 'Lyre\\Facet\\Repositories', 'Lyre\\Facet\\Repositories\\Contracts');
    }

    public function boot(): void
    {
        register_global_observers("Lyre\\Facet\\Models");

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        Livewire::component('lyre.facet.filament.relation-managers.facet-values-relation-manager', FacetValuesRelationManager::class);
    }
}
