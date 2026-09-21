<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Tequia\Finance\Models\Category;

/**
 * Acción reutilizable para eliminar una categoría. Sin restricción de
 * `hasMovements()` a propósito: las categorías se pueden borrar libremente,
 * sus movimientos quedan sin categoría (`nullOnDelete`) en vez de perderse
 * (ver docs/finance.md — Borrado protegido de cuentas y contextos).
 */
class DeleteCategoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteCategory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Eliminar')
            ->icon('heroicon-o-trash')
            ->iconButton()
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar categoría')
            ->modalDescription('¿Eliminar esta categoría? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?Category $record): void {
                ($record ?? Category::findOrFail($arguments['category']))->delete();
            });
    }
}
