<?php

namespace Lyre\School\Providers;

use Illuminate\Support\ServiceProvider;

class LyreSchoolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_repositories($this->app, 'Lyre\\School\\Repositories', 'Lyre\\School\\Repositories\\Contracts');

        // Register any other services here
    }

    public function boot(): void
    {
        register_global_observers("Lyre\\School\\Models");

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}

