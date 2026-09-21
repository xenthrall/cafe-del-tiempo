<?php

namespace Tequia\Finance\Filament\Resources\Movements\Actions;

use Filament\Actions\Action;
use Tequia\Finance\Models\Movement;

/**
 * Acción reutilizable para eliminar un movimiento. Recibe el id del movimiento
 * como argumento `movement` (ver ManageMovementAction para el mismo patrón).
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
            ->action(function (array $arguments): void {
                Movement::findOrFail($arguments['movement'])->delete();
            });
    }
}
