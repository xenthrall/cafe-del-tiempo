<?php

namespace Tequia\App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Vista previa de "Café IA", el futuro asistente integrado en la suite —
 * deliberadamente sin backend todavía (no envía ni responde nada), pero
 * con el pulido visual de una función ya terminada, como referencia de
 * hacia dónde va el producto.
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

    /**
     * Sugerencias de ejemplo, ancladas a lo que ya existe en la suite
     * (finanzas, bóveda) para que se sientan creíbles y no genéricas.
     *
     * @return array<int, string>
     */
    public function getSuggestions(): array
    {
        return [
            '¿Cuánto he gastado este mes?',
            'Resume el estado de mi bóveda',
            '¿En qué contexto financiero gasto más?',
            'Ayúdame a organizar mis cuentas',
        ];
    }
}
