<?php

namespace Tequia\Vault\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Tequia\Vault\VaultPlugin;

class VaultServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            // Solo lo registramos si el ID del panel es 'app'
            if ($panel->getId() === 'app') {
                $panel->plugin(VaultPlugin::make());
            }
        });
    }

    public function boot(): void {}
}
