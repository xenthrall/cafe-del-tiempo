<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tequia\Finance\Models\Account;

/**
 * Acción reutilizable para eliminar una cuenta. Antes de borrar comprueba
 * `Account::hasMovements()` — una cuenta con movimientos (propios o de
 * transferencias) no se puede perder sin perder histórico real (ver
 * docs/finance.md — Borrado protegido de cuentas y contextos). El
 * `restrictOnDelete()` a nivel de base de datos es el respaldo real; esto
 * solo evita que el usuario llegue a ese error sin avisarle antes.
 */
class DeleteAccountAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deleteAccount';
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
            ->modalHeading('Eliminar cuenta')
            ->modalDescription('¿Eliminar esta cuenta? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?Account $record, Action $action): void {
                $account = $record ?? Account::findOrFail($arguments['account']);

                if ($account->hasMovements()) {
                    Notification::make()
                        ->title('No se pudo eliminar la cuenta')
                        ->body('Tiene movimientos registrados. Elimínalos primero si de verdad quieres borrar la cuenta.')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $account->delete();
            });
    }
}
