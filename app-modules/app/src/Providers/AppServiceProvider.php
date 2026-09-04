<?php

namespace Tequia\App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AppPanelProvider::class);

        view()->prependNamespace(
            "filament-panels",
            __DIR__ . "/../../resources/views/vendor/filament-panels",
        );
    }

    public function boot(): void {}
}
