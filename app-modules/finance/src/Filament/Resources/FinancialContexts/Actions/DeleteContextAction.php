<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tequia\Finance\Models\FinancialContext;

/**
 * Acción reutilizable para eliminar un contexto financiero. Antes de borrar
 * comprueba `FinancialContext::hasMovements()` — un contexto con movimientos
 * no se puede perder sin perder histórico real (ver docs/finance.md —
 * Borrado protegido de cuentas y contextos). Sus categorías, si tiene,
 * quedan sin contexto (`nullOnDelete`) en vez de perderse. Sin `iconButton()`
 * a propósito: solo se usa agrupada en `<x-filament-actions::group>` (ver
 * manage-financial-contexts.blade.php), donde necesita mostrar su label para
 * no verse como un ícono suelto sin explicación.
 */
class DeleteContextAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteContext';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Eliminar')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar contexto')
            ->modalDescription('¿Eliminar este contexto? Sus categorías quedarán sin contexto.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?FinancialContext $record, Action $action): void {
                $context = $record ?? FinancialContext::findOrFail($arguments['context']);

                if ($context->hasMovements()) {
                    Notification::make()
                        ->title('No se pudo eliminar el contexto')
                        ->body('Tiene movimientos asociados. Archívalo si ya no lo usas.')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $context->delete();
            });
    }
}
