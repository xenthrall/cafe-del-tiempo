<?php

namespace Tequia\Finance\Filament\Resources\Movements\Pages;

use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Tequia\Finance\Filament\Resources\Movements\Actions\GenerateMovementsReportAction;
use Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction;
use Tequia\Finance\Filament\Resources\Movements\MovementResource;
use Tequia\Finance\Filament\Resources\Movements\Tables\MovementsTable;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;

class ManageMovements extends Page implements HasTable
{
    use HidesPageHeader;
    use InteractsWithTable;

    protected static string $resource = MovementResource::class;

    protected string $view = 'finance::filament.resources.movements.pages.manage-movements';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Movimientos';

    /**
     * Filtros — persistidos en la URL (`#[Url]`) para que sobrevivan a un
     * refresco de página; `except` los omite de la URL cuando están en su
     * valor por defecto, para no ensuciarla sin necesidad.
     */
    #[Url(as: 'type', except: 'all')]
    public string $activeType = 'all';

    /**
     * Ver MovementsTable — alterna entre el listado tipo tarjeta (por
     * defecto, vista Blade custom) y una tabla plana clásica. No es un
     * filtro, no se persiste en la URL.
     */
    public string $viewMode = MovementsTable::VIEW_MODE_CARDS;

    /**
     * Preset del filtro de periodo: null (todo el historial), 'week',
     * 'month', 'year' o 'custom' (usa $periodFrom/$periodUntil).
     */
    #[Url(as: 'period', except: null)]
    public ?string $periodPreset = null;

    #[Url(as: 'from', except: null)]
    public ?string $periodFrom = null;

    #[Url(as: 'until', except: null)]
    public ?string $periodUntil = null;

    #[Url(as: 'context', except: null)]
    public ?int $contextId = null;

    #[Url(as: 'category', except: null)]
    public ?int $categoryId = null;

    public function setActiveType(string $type): void
    {
        $this->activeType = $type;
        $this->resetTable();
    }

    public function setViewMode(string $viewMode): void
    {
        $this->viewMode = $viewMode;
        $this->resetTable();
    }

    public function updatedPeriodPreset(): void
    {
        if ($this->periodPreset !== 'custom') {
            $this->periodFrom = null;
            $this->periodUntil = null;
        }

        $this->resetTable();
    }

    public function updatedPeriodFrom(): void
    {
        $this->resetTable();
    }

    public function updatedPeriodUntil(): void
    {
        $this->resetTable();
    }

    public function updatedContextId(): void
    {
        $this->resetTable();
    }

    public function updatedCategoryId(): void
    {
        $this->resetTable();
    }

    /**
     * @return Collection<int, string>
     */
    public function contextOptions(): Collection
    {
        return FinancialContext::query()->orderBy('name')->pluck('name', 'id');
    }

    /**
     * Lista plana de categorías (ambos tipos, jerárquica) para el filtro —
     * a diferencia de ManageMovementAction, que solo ofrece las del tipo
     * elegido en el formulario. No se usa flatMap() aquí por la misma razón
     * documentada allí: colapsa las llaves enteras vía array_merge.
     *
     * @return Collection<int, string>
     */
    public function categoryOptions(): Collection
    {
        $options = [];

        Category::query()
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) use (&$options): void {
                $options[$category->id] = $category->name;

                foreach ($category->children as $child) {
                    $options[$child->id] = "{$category->name} > {$child->name}";
                }
            });

        return collect($options);
    }

    public function manageMovementAction(): ManageMovementAction
    {
        return ManageMovementAction::make();
    }

    public function generateMovementsReportAction(): GenerateMovementsReportAction
    {
        return GenerateMovementsReportAction::make();
    }

    public function table(Table $table): Table
    {
        return MovementsTable::configure($table, $this->filteredMovementsQuery(), $this->viewMode);
    }

    /**
     * Expuesta (no privada) para que `GenerateMovementsReportAction` genere
     * el informe sobre los mismos filtros que el usuario ve aplicados en
     * esta página — ver `Actions/GenerateMovementsReportAction`.
     */
    public function filteredMovementsQuery(): Builder
    {
        [$from, $until] = $this->periodDateRange();

        return Movement::query()
            ->with(['account', 'fromAccount', 'toAccount', 'category', 'financialContext'])
            ->when($this->activeType !== 'all', fn (Builder $query): Builder => $query->where('type', $this->activeType))
            ->when($this->contextId, fn (Builder $query): Builder => $query->where('financial_context_id', $this->contextId))
            ->when($this->categoryId, fn (Builder $query): Builder => $query->where('category_id', $this->categoryId))
            ->when($from, fn (Builder $query): Builder => $query->whereDate('date', '>=', $from))
            ->when($until, fn (Builder $query): Builder => $query->whereDate('date', '<=', $until));
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function periodDateRange(): array
    {
        return match ($this->periodPreset) {
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$this->periodFrom, $this->periodUntil],
            default => [null, null],
        };
    }

    /**
     * Descripción legible de los filtros activos, para el encabezado del
     * informe generado por `GenerateMovementsReportAction`.
     *
     * @return array<int, string>
     */
    public function activeFiltersSummary(): array
    {
        $summary = [];

        if ($this->activeType !== 'all') {
            $summary[] = 'Tipo: '.match ($this->activeType) {
                'income' => 'Ingresos',
                'expense' => 'Gastos',
                'transfer' => 'Transferencias',
                'adjustment' => 'Ajustes',
                default => $this->activeType,
            };
        }

        if ($this->periodPreset) {
            [$from, $until] = $this->periodDateRange();

            $summary[] = 'Periodo: '.match ($this->periodPreset) {
                'week' => 'Esta semana',
                'month' => 'Este mes',
                'year' => 'Este año',
                'custom' => $from && $until ? "{$from} al {$until}" : 'Rango personalizado',
                default => $this->periodPreset,
            };
        }

        if ($this->contextId) {
            $summary[] = 'Contexto: '.($this->contextOptions()->get($this->contextId) ?? '—');
        }

        if ($this->categoryId) {
            $summary[] = 'Categoría: '.($this->categoryOptions()->get($this->categoryId) ?? '—');
        }

        return $summary;
    }

    /**
     * Cuántos de los filtros en el panel colapsable (periodo, contexto,
     * categoría) están activos — para el badge del botón "Filtros" en móvil
     * (ver manage-movements.blade.php). No cuenta `activeType`: esas son las
     * pestañas de tipo, que quedan siempre visibles fuera del panel.
     */
    public function activeFilterCount(): int
    {
        return collect([$this->periodPreset, $this->contextId, $this->categoryId])
            ->filter(fn (mixed $value): bool => filled($value))
            ->count();
    }
}
