<?php

namespace Tequia\System\Providers;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(SystemPanelProvider::class);
    }

    public function boot(): void
    {
        // Solo la base de datos (--only-db): el código ya está en git, y un
        // backup de archivos incluiría el .env con credenciales/APP_KEY.
        Schedule::command('backup:run --only-db')
            ->daily()
            ->at('02:00')
            ->onOneServer();

        Schedule::command('backup:clean')
            ->daily()
            ->at('01:30')
            ->onOneServer();
    }
}
