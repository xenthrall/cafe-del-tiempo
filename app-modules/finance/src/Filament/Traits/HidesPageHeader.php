<?php

namespace Tequia\Finance\Filament\Traits;

/**
 * Oculta el encabezado (título + migas de pan) que Filament renderiza por
 * defecto en cada página. `finance` usa páginas completamente custom (ver
 * docs/finance.md — Interfaz Filament) y ese encabezado genérico acentúa la
 * sensación de "panel de administración" que el módulo busca evitar; cada
 * página que use este trait debe darle su propio contexto en la vista Blade.
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
