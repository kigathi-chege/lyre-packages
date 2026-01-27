<?php

namespace Lyre\Guest\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LyreGuestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_repositories($this->app, 'Lyre\\Guest\\Repositories', 'Lyre\\Guest\\Contracts');
        require_once base_path('vendor/lyre/guest/src/helpers/helpers.php');
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::subscribe(\Lyre\Guest\Listeners\UserEventSubscriber::class);

        register_global_observers("Lyre\\Guest\\Models");

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        // $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
