<?php

namespace Tequia\System\Providers;

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
use Tequia\System\Livewire\Sidebar;
use Tequia\System\Livewire\Topbar;

class SystemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system')
            ->path('system')
            ->login()
            ->viteTheme('resources/css/filament/system/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarLivewireComponent(Sidebar::class)
            ->topbarLivewireComponent(Topbar::class)
            ->brandLogo(asset('images/logo-light.png'))
            ->darkModeBrandLogo(asset('images/logo-dark.png'))
            ->brandLogoHeight('5rem')
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(
                in: __DIR__.'/../Filament/Resources',
                for: 'Tequia\\System\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/../Filament/Pages',
                for: 'Tequia\\System\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/../Filament/Widgets',
                for: 'Tequia\\System\\Filament\\Widgets',
            )
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
