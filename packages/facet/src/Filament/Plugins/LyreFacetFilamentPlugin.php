<?php

namespace Lyre\Facet\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;

class LyreFacetFilamentPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'lyre.facet';
    }

    public function register(Panel $panel): void
    {
        $resources = get_filament_resources_for_namespace('Lyre\\Facet\\Filament\\Resources');
        $panel->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
