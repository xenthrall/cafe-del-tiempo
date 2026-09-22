<?php

namespace Tequia\System\Filament\Traits;

/**
 * Oculta el encabezado (título + migas de pan) que Filament renderiza por
 * defecto en cada página. `system` usa páginas completamente custom, igual
 * que `finance` (ver `Tequia\Finance\Filament\Traits\HidesPageHeader`), y ese
 * encabezado genérico acentúa la sensación de "panel de administración"
 * tradicional; cada página que use este trait debe darle su propio contexto
 * en la vista Blade.
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
