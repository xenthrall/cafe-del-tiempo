<?php

namespace Tequia\System\Filament\Resources\Users\Actions;

use App\Models\User;
use Filament\Actions\Action;

/**
 * Acción reutilizable para eliminar un usuario. Acepta el usuario como
 * `recordAction` de una Table de Filament (registro enlazado directo) o
 * suelta, con `->arguments(['user' => $id])` (ver ManageUserAction para el
 * mismo patrón dual). El bloqueo real (no borrarse a sí mismo, no dejar la
 * instancia sin administradores) vive en `App\Models\User::booted()`, que
 * cancela el borrado y envía su propia notificación si corresponde.
 */
class DeleteUserAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteUser';
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
            ->modalHeading('Eliminar usuario')
            ->modalDescription('¿Eliminar este usuario? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?User $record): void {
                ($record ?? User::findOrFail($arguments['user']))->delete();
            });
    }
}
