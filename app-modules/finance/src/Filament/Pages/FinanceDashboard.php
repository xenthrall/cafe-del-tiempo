<?php

namespace Tequia\Finance\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Resources\Movements\Actions\DeleteMovementAction;
use Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction;
use Tequia\Finance\Filament\Resources\Movements\MovementResource;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;
use Tequia\Finance\Support\Money;
use UnitEnum;

/**
 * Resumen financiero — no es solo un tablero de solo-lectura: reutiliza las
 * acciones de `Movements` (`ManageMovementAction`/`DeleteMovementAction`,
 * ver docs/finance.md) para poder registrar/editar/borrar un movimiento sin
 * salir de aquí, y tiene sus propios filtros de periodo/contexto — igual
 * que `ManageMovements`, pero aplicados a todo el resumen a la vez, no solo
 * a un listado.
 */
class FinanceDashboard extends Page
{
    use HidesPageHeader;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Resumen';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 1;

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Resumen financiero';

    protected string $view = 'finance::filament.pages.finance-dashboard';

    /**
     * Filtros — persistidos en la URL, mismo patrón que `ManageMovements`.
     * 'month' por defecto: un resumen sin filtrar tiene más sentido
     * mostrando "cómo voy este mes" que un acumulado de todo el histórico.
     */
    #[Url(as: 'period', except: 'month')]
    public string $periodPreset = 'month';

    #[Url(as: 'from', except: null)]
    public ?string $periodFrom = null;

    #[Url(as: 'until', except: null)]
    public ?string $periodUntil = null;

    #[Url(as: 'context', except: null)]
    public ?int $contextId = null;

    public string $totalBalance = '$ 0,00';

    public string $periodIncome = '$ 0,00';

    public string $periodExpense = '$ 0,00';

    public string $periodNet = '$ 0,00';

    public bool $periodNetIsNegative = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $accounts = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $monthlyCashflow = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $recentMovements = [];

    /**
     * "Gastos por contexto" sin filtro de contexto, "Gastos por categoría"
     * cuando ya se filtró a un contexto — ver `loadBreakdown()`.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $expenseBreakdown = [];

    public string $expenseBreakdownLabel = 'Gastos por contexto';

    /**
     * Mismo criterio que `$expenseBreakdown`, para ingresos.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $incomeBreakdown = [];

    public string $incomeBreakdownLabel = 'Ingresos por contexto';

    /**
     * Glosario de conceptos del módulo, mostrado colapsado al final del
     * resumen (ver `finance-dashboard.blade.php`) para no saturar el
     * dashboard con explicaciones que el usuario no siempre necesita.
     *
     * @return array<int, array{term: string, definition: string}>
     */
    public function glossary(): array
    {
        return [
            [
                'term' => 'Movimiento',
                'definition' => 'Cada entrada o salida de dinero que registras: un ingreso, un gasto, una transferencia entre tus propias cuentas o un ajuste de saldo.',
            ],
            [
                'term' => 'Cuenta',
                'definition' => 'Dónde está el dinero: efectivo, una cuenta bancaria, una billetera digital o una tarjeta de crédito.',
            ],
            [
                'term' => 'Categoría',
                'definition' => 'En qué se gastó o de dónde vino un ingreso, por ejemplo "Transporte" o "Salario". Ayuda a entender en qué se va o de dónde llega el dinero.',
            ],
            [
                'term' => 'Contexto financiero',
                'definition' => 'El ámbito o actividad al que pertenece un movimiento, como "Personal" o "Vehículo". Sirve para separar y analizar finanzas distintas por aparte.',
            ],
            [
                'term' => 'Transferencia',
                'definition' => 'Mover dinero de una de tus cuentas a otra. No es un ingreso ni un gasto, porque el dinero sigue siendo tuyo.',
            ],
            [
                'term' => 'Ajuste',
                'definition' => 'Una corrección manual del saldo de una cuenta (por ejemplo, para cuadrarlo con el banco) sin registrar un ingreso o gasto que en realidad no ocurrió.',
            ],
            [
                'term' => 'Archivar',
                'definition' => 'Ocultar una cuenta, categoría o contexto de las opciones al crear un nuevo movimiento, sin borrar su historial ni afectar los movimientos ya registrados.',
            ],
            [
                'term' => 'Neto',
                'definition' => 'La diferencia entre tus ingresos y tus gastos en el periodo. Si es negativo, gastaste más de lo que entró.',
            ],
        ];
    }

    public function mount(): void
    {
        $this->refreshDashboard();
    }

    public function updatedPeriodPreset(): void
    {
        if ($this->periodPreset !== 'custom') {
            $this->periodFrom = null;
            $this->periodUntil = null;
        }

        $this->refreshDashboard();
    }

    public function updatedPeriodFrom(): void
    {
        $this->refreshDashboard();
    }

    public function updatedPeriodUntil(): void
    {
        $this->refreshDashboard();
    }

    public function updatedContextId(): void
    {
        $this->refreshDashboard();
    }

    /**
     * @return Collection<int, string>
     */
    public function contextOptions(): Collection
    {
        return FinancialContext::query()->orderBy('name')->pluck('name', 'id');
    }

    public function manageMovementAction(): ManageMovementAction
    {
        return ManageMovementAction::make()->after(fn () => $this->refreshDashboard());
    }

    public function deleteMovementAction(): DeleteMovementAction
    {
        return DeleteMovementAction::make()->after(fn () => $this->refreshDashboard());
    }

    public function movementsUrl(): string
    {
        return MovementResource::getUrl();
    }

    private function refreshDashboard(): void
    {
        $this->loadAccounts();
        $this->loadPeriodSummary();
        $this->loadMonthlyCashflow();
        $this->loadRecentMovements();
        $this->loadExpenseBreakdown();
        $this->loadIncomeBreakdown();
    }

    private function loadAccounts(): void
    {
        // Todas las cuentas, también las archivadas: archivar solo las oculta
        // de los selectores de movimientos nuevos, el dinero que queda en
        // ellas sigue siendo real y debe contar en el saldo total.
        $accounts = Account::query()
            ->with(['movements', 'outgoingTransfers', 'incomingTransfers'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $total = $accounts->reduce(fn (string $carry, Account $account): string => bcadd($carry, $account->balance(), 2), '0');

        $this->totalBalance = Money::format($total);

        $this->accounts = $accounts
            ->map(fn (Account $account): array => [
                'id' => $account->id,
                'name' => $account->name,
                'icon' => $account->type->icon(),
                'formattedBalance' => $account->formattedBalance(),
                'isNegative' => bccomp($account->balance(), '0', 2) < 0,
                'isActive' => $account->is_active,
            ])
            ->all();
    }

    private function loadPeriodSummary(): void
    {
        [$start, $end] = $this->periodDateRange();

        $baseQuery = fn (MovementType $type) => Movement::query()
            ->where('type', $type)
            ->when($start, fn ($query) => $query->whereDate('date', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('date', '<=', $end))
            ->when($this->contextId, fn ($query) => $query->where('financial_context_id', $this->contextId));

        $income = (string) $baseQuery(MovementType::Income)->sum('amount');
        $expense = (string) $baseQuery(MovementType::Expense)->sum('amount');
        $net = bcsub($income, $expense, 2);

        $this->periodIncome = Money::format($income);
        $this->periodExpense = Money::format($expense);
        $this->periodNet = Money::format($net);
        $this->periodNetIsNegative = bccomp($net, '0', 2) < 0;
    }

    private function loadMonthlyCashflow(): void
    {
        $summaries = collect(range(5, 0))
            ->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth())
            ->map(function (Carbon $month): array {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $baseQuery = fn (MovementType $type) => Movement::query()
                    ->where('type', $type)
                    ->whereBetween('date', [$start, $end])
                    ->when($this->contextId, fn ($query) => $query->where('financial_context_id', $this->contextId));

                $income = (float) $baseQuery(MovementType::Income)->sum('amount');
                $expense = (float) $baseQuery(MovementType::Expense)->sum('amount');

                return [
                    'label' => ucfirst($month->translatedFormat('M Y')),
                    'income' => $income,
                    'expense' => $expense,
                    'formattedIncome' => Money::format($income),
                    'formattedExpense' => Money::format($expense),
                ];
            });

        $max = $summaries->flatMap(fn (array $summary): array => [$summary['income'], $summary['expense']])->max() ?: 1;

        $this->monthlyCashflow = $summaries
            ->map(fn (array $summary): array => [
                ...$summary,
                'incomePercent' => $max > 0 ? (int) round(($summary['income'] / $max) * 100) : 0,
                'expensePercent' => $max > 0 ? (int) round(($summary['expense'] / $max) * 100) : 0,
            ])
            ->all();
    }

    private function loadRecentMovements(): void
    {
        [$start, $end] = $this->periodDateRange();

        $this->recentMovements = Movement::query()
            ->with(['account', 'fromAccount', 'toAccount', 'category', 'financialContext'])
            ->when($start, fn ($query) => $query->whereDate('date', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('date', '<=', $end))
            ->when($this->contextId, fn ($query) => $query->where('financial_context_id', $this->contextId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Movement $movement): array => [
                'id' => $movement->id,
                'typeLabel' => $movement->type->label(),
                'typeColor' => $movement->type->color(),
                'typeIcon' => $movement->type->icon(),
                'accountsLabel' => $movement->accountsLabel(),
                'categoryName' => $movement->category?->name,
                'contextName' => $movement->financialContext?->name,
                'formattedAmount' => $movement->formattedAmount(),
                'isNegative' => $movement->type === MovementType::Expense
                    || ($movement->type === MovementType::Adjustment && $movement->amount < 0),
                'date' => $movement->date->format('d/m/Y'),
            ])
            ->all();
    }

    private function loadExpenseBreakdown(): void
    {
        [$this->expenseBreakdownLabel, $this->expenseBreakdown] = $this->loadBreakdown(MovementType::Expense, 'Gastos');
    }

    private function loadIncomeBreakdown(): void
    {
        [$this->incomeBreakdownLabel, $this->incomeBreakdown] = $this->loadBreakdown(MovementType::Income, 'Ingresos');
    }

    /**
     * Sin contexto filtrado: desglose por contexto, para ver dónde mirar.
     * Con un contexto ya elegido, ese desglose sería una sola barra (el
     * mismo contexto) — en su lugar, desglosa por categoría *dentro* de ese
     * contexto, que es la pregunta que de verdad importa en ese momento.
     * Usado tanto para gastos como para ingresos (ver `loadExpenseBreakdown()`/
     * `loadIncomeBreakdown()`).
     *
     * @return array{0: string, 1: array<int, array<string, mixed>>}
     */
    private function loadBreakdown(MovementType $type, string $noun): array
    {
        [$start, $end] = $this->periodDateRange();

        $query = Movement::query()
            ->where('type', $type)
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end))
            ->when($this->contextId, fn ($q) => $q->where('financial_context_id', $this->contextId));

        if ($this->contextId) {
            $label = "{$noun} por categoría";

            $rows = $query->with('category')
                ->get()
                ->groupBy(fn (Movement $movement): string => $movement->category?->name ?? 'Sin categoría');
        } else {
            $label = "{$noun} por contexto";

            $rows = $query->with('financialContext')
                ->get()
                ->groupBy(fn (Movement $movement): string => $movement->financialContext?->name ?? 'Sin contexto');
        }

        $rows = $rows
            ->map(fn (Collection $movements, string $name): array => ['name' => $name, 'total' => (float) $movements->sum('amount')])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $max = $rows->max('total') ?: 1;

        $breakdown = $rows
            ->map(fn (array $row): array => [
                ...$row,
                'formattedTotal' => Money::format($row['total']),
                'percent' => $max > 0 ? (int) round(($row['total'] / $max) * 100) : 0,
            ])
            ->all();

        return [$label, $breakdown];
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
}
