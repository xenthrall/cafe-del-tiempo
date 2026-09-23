<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Support\PersonalContextTemplate;

/**
 * Arranque rápido para un usuario sin contextos todavía: crea el contexto
 * "Personal" con el árbol de categorías de `PersonalContextTemplate` ya
 * organizado en gastos e ingresos, para que pueda empezar a registrar
 * movimientos sin construir su propia estructura desde cero (ver el bloque
 * `@empty` en manage-financial-contexts.blade.php, donde se ofrece el botón).
 * No necesita `arguments`/binding de registro porque no edita nada existente.
 */
class CreateSampleContextAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'createSampleContext';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Crear contexto de ejemplo (Personal)')
            ->icon('heroicon-o-sparkles')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Crear contexto de ejemplo')
            ->modalDescription('Se creará un contexto "Personal" con categorías de gastos e ingresos ya organizadas, para que puedas empezar a registrar tus movimientos de inmediato. Podrás editarlas o borrarlas después.')
            ->modalSubmitActionLabel('Crear')
            ->action(function (): void {
                $this->create();
            });
    }

    private function create(): void
    {
        DB::transaction(function (): void {
            $context = FinancialContext::create([
                'name' => 'Personal',
                'is_active' => true,
            ]);

            foreach (PersonalContextTemplate::categories() as $type => $parents) {
                foreach ($parents as $parentName => $children) {
                    $parent = Category::create([
                        'name' => $parentName,
                        'type' => $type,
                        'financial_context_id' => $context->id,
                        'is_active' => true,
                    ]);

                    foreach ($children as $childName) {
                        Category::create([
                            'name' => $childName,
                            'type' => $type,
                            'parent_id' => $parent->id,
                            'financial_context_id' => $context->id,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        });

        Notification::make()
            ->title('Contexto "Personal" creado')
            ->body('Ya puedes registrar movimientos usando las categorías de ejemplo.')
            ->success()
            ->send();
    }
}
