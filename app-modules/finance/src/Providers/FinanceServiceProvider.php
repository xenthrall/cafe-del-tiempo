<?php

namespace Tequia\Finance\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Tequia\Finance\FinancePlugin;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() === 'app') {
                $panel->plugin(FinancePlugin::make());
            }
        });
    }

    public function boot(): void {}
}
