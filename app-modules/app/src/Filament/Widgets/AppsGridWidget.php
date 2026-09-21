<?php

namespace Tequia\App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Lanzador de módulos, estilo "app grid" (como el de Odoo, pero con un
 * diseño propio más moderno): centraliza el acceso a cada módulo de la
 * suite desde el dashboard. Sumar un módulo nuevo en el futuro es agregar
 * una entrada a `getApps()`, nada más — no hay un sistema de registro por
 * módulo todavía porque solo hay dos, sería una abstracción prematura.
 */
class AppsGridWidget extends Widget
{
    protected string $view = 'app::filament.widgets.apps-grid-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * Después de `WelcomeWidget` (sort -1 por defecto), para que el saludo
     * quede primero y el lanzador de módulos justo debajo.
     */
    protected static ?int $sort = 1;

    /**
     * @return array<int, array{label: string, description: string, icon: string, url: string, accent: string}>
     */
    public function getApps(): array
    {
        return [
            [
                'label' => 'Bóveda',
                'description' => 'Contraseñas, secretos, notas confidenciales y cápsulas del tiempo.',
                'icon' => 'heroicon-o-lock-closed',
                'url' => route('filament.app.pages.vault-dashboard'),
                'accent' => 'amber',
            ],
            [
                'label' => 'Finanzas',
                'description' => 'Movimientos, cuentas, contextos financieros e informes.',
                'icon' => 'heroicon-o-chart-pie',
                'url' => route('filament.app.pages.finance-dashboard'),
                'accent' => 'emerald',
            ],
        ];
    }
}
