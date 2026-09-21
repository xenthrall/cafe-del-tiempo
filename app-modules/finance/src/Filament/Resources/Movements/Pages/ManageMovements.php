<?php

namespace Tequia\Finance\Filament\Resources\Movements\Pages;

use Filament\Resources\Pages\Page;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Resources\Movements\Actions\DeleteMovementAction;
use Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction;
use Tequia\Finance\Filament\Resources\Movements\MovementResource;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\Movement;

class ManageMovements extends Page
{
    use HidesPageHeader;

    protected static string $resource = MovementResource::class;

    protected string $view = 'finance::filament.resources.movements.pages.manage-movements';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Movimientos';

    /**
     * Cuántos movimientos recientes se listan. Un vault/finance single-user no
     * necesita paginación real todavía (ver docs/finance.md); si el volumen lo
     * exige más adelante, se reevalúa.
     */
    private const MOVEMENTS_LIMIT = 200;

    public string $activeType = 'all';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $movements = [];

    public function mount(): void
    {
        $this->refreshMovements();
    }

    public function setActiveType(string $type): void
    {
        $this->activeType = $type;
        $this->refreshMovements();
    }

    public function manageMovementAction(): ManageMovementAction
    {
        return ManageMovementAction::make()->after(
            fn () => $this->refreshMovements(),
        );
    }

    public function deleteMovementAction(): DeleteMovementAction
    {
        return DeleteMovementAction::make()->after(
            fn () => $this->refreshMovements(),
        );
    }

    private function refreshMovements(): void
    {
        $this->movements = Movement::query()
            ->with([
                'account',
                'fromAccount',
                'toAccount',
                'category',
                'financialContext',
            ])
            ->when(
                $this->activeType !== 'all',
                fn ($query) => $query->where('type', $this->activeType),
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(self::MOVEMENTS_LIMIT)
            ->get()
            ->map(
                fn (Movement $movement): array => $this->serializeMovement(
                    $movement,
                ),
            )
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMovement(Movement $movement): array
    {
        return [
            'id' => $movement->id,
            'type' => $movement->type->value,
            'typeLabel' => $movement->type->label(),
            'typeColor' => $movement->type->color(),
            'typeIcon' => $movement->type->icon(),
            'accountsLabel' => $movement->accountsLabel(),
            'categoryName' => $movement->category?->name,
            'contextName' => $movement->financialContext?->name,
            'formattedAmount' => $movement->formattedAmount(),
            'isNegative' => in_array($movement->type, [MovementType::Expense], true) ||
                ($movement->type === MovementType::Adjustment &&
                    $movement->amount < 0),
            'date' => $movement->date->format('d/m/Y'),
            'description' => $movement->description,
        ];
    }
}
