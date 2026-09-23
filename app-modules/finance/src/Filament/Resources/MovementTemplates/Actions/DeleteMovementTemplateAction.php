<?php

namespace Tequia\Finance\Filament\Resources\MovementTemplates\Actions;

use Filament\Actions\Action;
use Tequia\Finance\Models\MovementTemplate;

/**
 * Acción reutilizable para eliminar una plantilla de movimiento frecuente.
 * A diferencia de `DeleteAccountAction`/`DeleteCategoryAction`, no hay guard
 * previo: nada referencia `movement_templates.id` por FK, así que borrar una
 * plantilla no pone en riesgo ningún histórico (los movimientos ya creados a
 * partir de ella son independientes). Sin `iconButton()` a propósito: solo
 * se usa agrupada en `<x-filament-actions::group>` (ver
 * manage-movement-templates.blade.php), donde necesita mostrar su label para
 * no verse como un ícono suelto sin explicación.
 */
class DeleteMovementTemplateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteMovementTemplate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Eliminar')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar plantilla')
            ->modalDescription('¿Eliminar esta plantilla? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?MovementTemplate $record): void {
                ($record ?? MovementTemplate::findOrFail($arguments['template']))->delete();
            });
    }
}
