<?php

namespace Tequia\Vault\Filament\Traits;

/**
 * Oculta el encabezado (título + migas de pan) que Filament renderiza por
 * defecto en cada página, igual que el trait homónimo de `finance`. Se
 * duplica aquí a propósito para que `vault` no dependa de otro módulo; cada
 * página que lo use debe darle su propio encabezado en la vista Blade.
 */
trait HidesPageHeader
{
    public function getHeading(): string
    {
        return '';
    }

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [];
    }
}
