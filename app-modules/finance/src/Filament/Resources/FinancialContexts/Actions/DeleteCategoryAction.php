<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tequia\Finance\Models\Category;

/**
 * Acción reutilizable para eliminar una categoría. Antes de borrar comprueba
 * `Category::hasMovements()` — una categoría con movimientos no se puede
 * perder sin perder histórico real (`movements.category_id` usa
 * `restrictOnDelete()` — ver docs/finance.md). Se archiva en su lugar. Sin
 * `iconButton()` a propósito: solo se usa agrupada en
 * `<x-filament-actions::group>` (ver manage-financial-contexts.blade.php),
 * donde necesita mostrar su label para no verse como un ícono suelto sin
 * explicación.
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
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar categoría')
            ->modalDescription('¿Eliminar esta categoría? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Eliminar')
            ->action(function (array $arguments, ?Category $record, Action $action): void {
                $category = $record ?? Category::findOrFail($arguments['category']);

                if ($category->hasMovements()) {
                    Notification::make()
                        ->title('No se pudo eliminar la categoría')
                        ->body('Tiene movimientos registrados. Archívala si ya no la usas, para conservar el histórico.')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $category->delete();
            });
    }
}
