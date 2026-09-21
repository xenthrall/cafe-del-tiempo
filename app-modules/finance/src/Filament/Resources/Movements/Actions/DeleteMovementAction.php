<?php

namespace Tequia\Finance\Filament\Resources\Movements\Actions;

use Filament\Actions\Action;
use Tequia\Finance\Models\Movement;

/**
 * Acción reutilizable para eliminar un movimiento. Acepta el movimiento como
 * `recordAction` de una Table de Filament (registro enlazado directo) o
 * suelta, con `->arguments(['movement' => $id])` (ver ManageMovementAction
 * para el mismo patrón).
 */
class DeleteMovementAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteMovement';
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
            ->modalHeading('Eliminar movimiento')
            ->modalDescription('¿Eliminar este movimiento? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?Movement $record): void {
                ($record ?? Movement::findOrFail($arguments['movement']))->delete();
            });
    }
}
