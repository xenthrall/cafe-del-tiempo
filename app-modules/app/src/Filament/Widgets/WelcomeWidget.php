<?php

namespace Tequia\App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Str;

/**
 * Saludo del dashboard: fecha, saludo según la hora y el nombre de pila del
 * usuario. Deliberadamente breve — el protagonista del dashboard es el
 * lanzador de módulos (`AppsGridWidget`) que va justo debajo.
 */
class WelcomeWidget extends Widget
{
    protected string $view = 'app::filament.widgets.welcome-widget';

    protected int|string|array $columnSpan = 'full';

    public function getGreeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 12 => 'Buenos días',
            $hour < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };
    }

    public function getFirstName(): string
    {
        return Str::before(trim(filament()->auth()->user()->name), ' ');
    }

    public function getToday(): string
    {
        return now()->translatedFormat('l, j \d\e F');
    }
}
