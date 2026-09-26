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
     * Cada módulo lleva sus accesos directos para que el usuario llegue a la
     * sección que busca con un solo toque, sin abrir el menú lateral (que en
     * móvil queda escondido detrás del botón de hamburguesa).
     *
     * @return array<int, array{label: string, description: string, icon: string, url: string, accent: string, shortcuts: array<int, array{label: string, icon: string, url: string}>}>
     */
    public function getApps(): array
    {
        return [
            [
                'label' => 'Bóveda',
                'description' => 'Contraseñas, notas seguras y códigos de recuperación, cifrados en tu navegador.',
                'icon' => 'heroicon-o-lock-closed',
                'url' => route('filament.app.pages.vault-dashboard'),
                'accent' => 'amber',
                'shortcuts' => [],
            ],
            [
                'label' => 'Finanzas',
                'description' => 'Movimientos, cuentas, contextos financieros e informes.',
                'icon' => 'heroicon-o-chart-pie',
                'url' => route('filament.app.pages.finance-dashboard'),
                'accent' => 'emerald',
                'shortcuts' => [
                    ['label' => 'Movimientos', 'icon' => 'heroicon-o-arrows-right-left', 'url' => route('filament.app.resources.movements.index')],
                    ['label' => 'Cuentas', 'icon' => 'heroicon-o-wallet', 'url' => route('filament.app.resources.accounts.index')],
                    ['label' => 'Frecuentes', 'icon' => 'heroicon-o-bolt', 'url' => route('filament.app.resources.movement-templates.index')],
                    ['label' => 'Contextos', 'icon' => 'heroicon-o-tag', 'url' => route('filament.app.resources.financial-contexts.index')],
                ],
            ],
        ];
    }
}
