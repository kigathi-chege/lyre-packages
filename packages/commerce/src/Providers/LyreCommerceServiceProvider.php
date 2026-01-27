<?php

namespace Lyre\Commerce\Providers;

use Illuminate\Support\ServiceProvider;

class LyreCommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_repositories($this->app, 'Lyre\\Commerce\\Repositories', 'Lyre\\Commerce\\Repositories\\Contracts');

        // Extend Lyre discovery paths to include Commerce package
        $this->mergePathConfig('model', 'Lyre\\Commerce\\Models');
        $this->mergePathConfig('repository', 'Lyre\\Commerce\\Repositories');
        $this->mergePathConfig('contracts', 'Lyre\\Commerce\\Repositories\\Contracts');
        $this->mergePathConfig('resource', 'Lyre\\Commerce\\Http\\Resources');
        $this->mergePathConfig('request', 'Lyre\\Commerce\\Http\\Requests');
    }

    public function boot(): void
    {
        register_global_observers('Lyre\\Commerce\\Models');

        // Register custom OrderObserver for fulfillment events
        // if (class_exists(\Lyre\Commerce\Models\Order::class)) {
        //     \Lyre\Commerce\Models\Order::observe(\Lyre\Commerce\Observers\OrderObserver::class);
        // }

        $this->publishes([
            __DIR__ . '/../config/commerce.php' => config_path('commerce.php'),
        ], 'lyre-commerce-config');

        $this->publishes([
            __DIR__ . '/../Database/migrations' => database_path('migrations'),
        ], 'lyre-commerce-migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lyre\Commerce\Console\Commands\InstallCommerceCommand::class,
                \Lyre\Commerce\Console\Commands\ResetCommerceData::class,
            ]);
        }

        // Note: Filament plugin should be registered in PanelProvider (e.g., AdminPanelProvider)
        // Add: Lyre\Commerce\Filament\Plugins\LyreCommerceFilamentPlugin::make()
    }

    private function mergePathConfig(string $key, string $namespace): void
    {
        $paths = config('lyre.path.' . $key, []);
        if (!in_array($namespace, $paths, true)) {
            $paths[] = $namespace;
            config(['lyre.path.' . $key => $paths]);
        }
    }
}
