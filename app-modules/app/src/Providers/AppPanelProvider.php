<?php

namespace Tequia\App\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Tequia\App\Livewire\Sidebar;
use Tequia\App\Livewire\Topbar;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->profile(isSimple: false)
            ->login()
            ->viteTheme('resources/css/filament/app/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarLivewireComponent(Sidebar::class)
            ->topbarLivewireComponent(Topbar::class)
            ->colors([
                'danger' => Color::Rose,
                // 'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(
                in: __DIR__.'/../Filament/Resources',
                for: 'Tequia\\App\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/../Filament/Pages',
                for: 'Tequia\\App\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/../Filament/Widgets',
                for: 'Tequia\\App\\Filament\\Widgets',
            )
            ->pages([
                //
            ])
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
