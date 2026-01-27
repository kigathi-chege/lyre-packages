<?php

namespace Lyre\File\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;

class LyreFileFilamentPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'lyre.file';
    }

    public function register(Panel $panel): void
    {
        $resources = get_filament_resources_for_namespace('Lyre\\File\\Filament\\Resources');
        $panel->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
