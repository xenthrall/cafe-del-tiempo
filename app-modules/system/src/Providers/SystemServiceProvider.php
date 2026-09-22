<?php

namespace Tequia\System\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(SystemPanelProvider::class);
    }

    public function boot(): void
    {
        require __DIR__.'/../../routes/console.php';
    }
}
