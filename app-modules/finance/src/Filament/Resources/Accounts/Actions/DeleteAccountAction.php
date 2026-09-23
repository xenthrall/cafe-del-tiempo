<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tequia\Finance\Models\Account;

/**
 * Acción reutilizable para eliminar una cuenta. Antes de borrar comprueba
 * `Account::hasMovements()` — una cuenta con movimientos (propios o de
 * transferencias) no se puede perder sin perder histórico real (ver
 * docs/finance.md — Borrado protegido de cuentas y contextos) — y
 * `Account::hasMovementTemplates()`, porque una plantilla de movimiento
 * frecuente sin cuenta queda inutilizable. El `restrictOnDelete()` a nivel de
 * base de datos es el respaldo real en ambos casos; esto solo evita que el
 * usuario llegue a ese error sin avisarle antes. Sin `iconButton()` a
 * propósito: solo se usa agrupada en `<x-filament-actions::group>` (ver
 * manage-accounts.blade.php), donde necesita mostrar su label para no verse
 * como un ícono suelto sin explicación.
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
                        ->body('Tiene movimientos registrados. Archívala si ya no la usas, para conservar el histórico.')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                if ($account->hasMovementTemplates()) {
                    Notification::make()
                        ->title('No se pudo eliminar la cuenta')
                        ->body('Tiene plantillas de movimientos frecuentes que la usan. Edítalas o elimínalas primero, o archiva la cuenta en su lugar.')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $account->delete();
            });
    }
}
