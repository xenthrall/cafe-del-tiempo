<?php

namespace Tequia\Finance;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FinancePlugin implements Plugin
{
    public function getId(): string
    {
        return 'tequia-finance';
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
                for: 'Tequia\\Finance\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Tequia\\Finance\\Filament\\Pages',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
