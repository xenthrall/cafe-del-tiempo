<?php

namespace Tequia\Vault;

use Filament\Contracts\Plugin;
use Filament\Panel;

class VaultPlugin implements Plugin
{
    public function getId(): string
    {
        return 'tequia-vault';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'Tequia\\Vault\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Tequia\\Vault\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Tequia\\Vault\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
