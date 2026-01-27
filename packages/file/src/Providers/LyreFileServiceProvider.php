<?php

namespace Lyre\File\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lyre\File\Livewire\FileGallery;

class LyreFileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_repositories($this->app, 'Lyre\\File\\Repositories', 'Lyre\\File\\Repositories\\Contracts');
    }

    public function boot(): void
    {
        register_global_observers("Lyre\\File\\Models");

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lyre.file');

        $this->publishes([
            __DIR__ . '/../resources/public' => public_path('lyre/file'),
        ]);

        Livewire::component('file-gallery', FileGallery::class);
    }
}
