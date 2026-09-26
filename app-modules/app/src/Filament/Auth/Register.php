<?php

namespace Tequia\App\Filament\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

/**
 * Registro con el mismo layout de marca que el login.
 */
class Register extends BaseRegister
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
            'asideHeading' => 'Protege hoy lo que trasciende en el tiempo.',
            'asideDescription' => 'Crea tu cuenta gratis y empieza con una bóveda cifrada en tu navegador y tus finanzas en orden.',
        ];
    }
}
