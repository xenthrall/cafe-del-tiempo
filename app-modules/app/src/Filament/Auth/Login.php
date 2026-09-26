<?php

namespace Tequia\App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

/**
 * Login con el layout de marca de la app (panel lateral + formulario), en
 * lugar de la tarjeta centrada por defecto de Filament.
 */
class Login extends BaseLogin
{
    protected static string $layout = 'app::filament.auth.layout';

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getLayoutData(): array
    {
        return [
            ...parent::getLayoutData(),
            'asideHeading' => 'Tu rincón de calma te estaba esperando.',
            'asideDescription' => 'Entra para abrir tu bóveda y revisar tus finanzas. Todo sigue donde lo dejaste.',
        ];
    }
}
