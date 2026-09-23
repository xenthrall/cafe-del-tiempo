<?php

namespace Tequia\Finance\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Tequia\Finance\Enums\Currency;
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

    /**
     * KPIs "por moneda" en vez de un solo total (ver docs/finance.md —
     * Multi-moneda): sin conversión automática, sumar cuentas/movimientos de
     * monedas distintas como si fueran una sola sería un número sin sentido.
     * Con una sola moneda en uso (el caso normal) cada arreglo trae una sola
     * entrada y la vista se ve igual que antes.
     *
     * @var array<int, array{currency: string, formatted: string}>
     */
    public array $totalBalances = [];

    /**
     * @var array<int, array{currency: string, formatted: string}>
     */
    public array $periodIncomes = [];

    /**
     * @var array<int, array{currency: string, formatted: string}>
     */
    public array $periodExpenses = [];

    /**
     * @var array<int, array{currency: string, formatted: string, isNegative: bool}>
     */
    public array $periodNets = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $accounts = [];

    /**
     * Igual que los KPIs, agrupado por moneda — una entrada por moneda en
     * uso, cada una con sus 6 meses.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    public array $monthlyCashflow = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $recentMovements = [];

    /**
     * "Gastos por contexto" sin filtro de contexto, "Gastos por categoría"
     * cuando ya se filtró a un contexto — ver `loadBreakdown()`. Agrupado
     * por moneda igual que los KPIs (ver `$totalBalances`).
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    public array $expenseBreakdown = [];

    public string $expenseBreakdownLabel = 'Gastos por contexto';

    /**
     * Mismo criterio que `$expenseBreakdown`, para ingresos.
     *
     * @var array<string, array<int, array<string, mixed>>>
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

        $this->refreshFilteredSections();
    }

    public function updatedPeriodFrom(): void
    {
        $this->refreshFilteredSections();
    }

    public function updatedPeriodUntil(): void
    {
        $this->refreshFilteredSections();
    }

    public function updatedContextId(): void
    {
        $this->refreshFilteredSections();
    }

    /**
     * @return Collection<int, string>
     */
    public function contextOptions(): Collection
    {
        return FinancialContext::query()->orderBy('name')->pluck('name', 'id');
    }

    /**
     * Cuántos filtros del panel colapsable (periodo, contexto) están activos
     * — para el badge del botón "Filtros" en móvil (ver finance-dashboard.blade.php).
     * 'month' es el default de $periodPreset, no cuenta como filtro activo.
     */
    public function activeFilterCount(): int
    {
        return collect([$this->periodPreset !== 'month', filled($this->contextId)])
            ->filter()
            ->count();
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

    /**
     * `loadAccounts()` no depende de ningún filtro de este dashboard (saldo
     * total = todo el histórico, siempre) — solo cambia cuando se
     * crea/edita/borra un movimiento real (ver `manageMovementAction()`/
     * `deleteMovementAction()`). Los filtros de periodo/contexto solo
     * afectan a las demás secciones, por eso tienen su propio
     * `refreshFilteredSections()` que se salta el recálculo de saldos.
     */
    private function refreshDashboard(): void
    {
        $this->loadAccounts();
        $this->refreshFilteredSections();
    }

    private function refreshFilteredSections(): void
    {
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

        $balancesByCurrency = $accounts
            ->groupBy('currency')
            ->map(fn (Collection $group): string => $group->reduce(
                fn (string $carry, Account $account): string => bcadd($carry, $account->balance(), 2),
                '0',
            ));

        $this->totalBalances = $this->activeCurrencies()
            ->map(fn (string $currency): array => [
                'currency' => $currency,
                'formatted' => Money::format($balancesByCurrency->get($currency, '0'), $currency),
            ])
            ->values()
            ->all();

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
            ->with('account:id,currency')
            ->where('type', $type)
            ->when($start, fn ($query) => $query->whereDate('date', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('date', '<=', $end))
            ->when($this->contextId, fn ($query) => $query->where('financial_context_id', $this->contextId));

        // bcadd, no Collection::sum() (que suma con el operador `+` de PHP,
        // en punto flotante) — mismo criterio que Account::balance(), para
        // no mezclar dos formas distintas de sumar dinero en la misma app.
        $sumAmounts = fn (Collection $group): string => $group->reduce(
            fn (string $carry, Movement $movement): string => bcadd($carry, (string) $movement->amount, 2),
            '0',
        );

        $incomeByCurrency = $baseQuery(MovementType::Income)->get()
            ->groupBy(fn (Movement $movement): string => $movement->currency())
            ->map($sumAmounts);

        $expenseByCurrency = $baseQuery(MovementType::Expense)->get()
            ->groupBy(fn (Movement $movement): string => $movement->currency())
            ->map($sumAmounts);

        $currencies = $incomeByCurrency->keys()->merge($expenseByCurrency->keys())->unique();
        $currencies = $currencies->isEmpty() ? $this->activeCurrencies() : $currencies->sort()->values();

        $this->periodIncomes = $currencies->map(fn (string $currency): array => [
            'currency' => $currency,
            'formatted' => Money::format($incomeByCurrency->get($currency, '0'), $currency),
        ])->all();

        $this->periodExpenses = $currencies->map(fn (string $currency): array => [
            'currency' => $currency,
            'formatted' => Money::format($expenseByCurrency->get($currency, '0'), $currency),
        ])->all();

        $this->periodNets = $currencies->map(function (string $currency) use ($incomeByCurrency, $expenseByCurrency): array {
            $net = bcsub($incomeByCurrency->get($currency, '0'), $expenseByCurrency->get($currency, '0'), 2);

            return [
                'currency' => $currency,
                'formatted' => Money::format($net, $currency),
                'isNegative' => bccomp($net, '0', 2) < 0,
            ];
        })->all();
    }

    private function loadMonthlyCashflow(): void
    {
        $months = collect(range(5, 0))->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth());

        $this->monthlyCashflow = $this->activeCurrencies()
            ->mapWithKeys(fn (string $currency): array => [$currency => $this->monthlyCashflowForCurrency($currency, $months)])
            ->all();
    }

    /**
     * @param  Collection<int, Carbon>  $months
     * @return array<int, array<string, mixed>>
     */
    private function monthlyCashflowForCurrency(string $currency, Collection $months): array
    {
        $summaries = $months->map(function (Carbon $month) use ($currency): array {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $baseQuery = fn (MovementType $type) => Movement::query()
                ->whereHas('account', fn ($query) => $query->where('currency', $currency))
                ->where('type', $type)
                ->whereBetween('date', [$start, $end])
                ->when($this->contextId, fn ($query) => $query->where('financial_context_id', $this->contextId));

            $income = (float) $baseQuery(MovementType::Income)->sum('amount');
            $expense = (float) $baseQuery(MovementType::Expense)->sum('amount');

            return [
                'label' => ucfirst($month->translatedFormat('M Y')),
                'income' => $income,
                'expense' => $expense,
                'formattedIncome' => Money::format($income, $currency),
                'formattedExpense' => Money::format($expense, $currency),
            ];
        });

        $max = $summaries->flatMap(fn (array $summary): array => [$summary['income'], $summary['expense']])->max() ?: 1;

        return $summaries
            ->map(fn (array $summary): array => [
                ...$summary,
                'incomePercent' => $max > 0 ? (int) round(($summary['income'] / $max) * 100) : 0,
                'expensePercent' => $max > 0 ? (int) round(($summary['expense'] / $max) * 100) : 0,
            ])
            ->all();
    }

    /**
     * Monedas en uso — todas las de las cuentas del usuario, sin duplicar.
     * Respaldo cuando un cálculo por moneda no encuentra ninguna (sin
     * cuentas, o sin movimientos en el rango filtrado): antes siempre había
     * un solo total, aunque fuera "$ 0,00" — con esto las tarjetas no
     * quedan vacías en ese caso.
     *
     * @return Collection<int, string>
     */
    private function activeCurrencies(): Collection
    {
        // pluck() hidrata un modelo parcial por fila para leer la columna, así
        // que sí aplica el cast de Account::currency — hay que desenvolver el
        // enum aquí, la única vez, para que el resto del dashboard siga
        // trabajando con strings planas como antes.
        $currencies = Account::query()->distinct()->pluck('currency')
            ->map(fn (Currency $currency): string => $currency->value);

        return $currencies->isEmpty() ? collect(['COP']) : $currencies->values();
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
     * @return array{0: string, 1: array<string, array<int, array<string, mixed>>>}
     */
    private function loadBreakdown(MovementType $type, string $noun): array
    {
        [$start, $end] = $this->periodDateRange();

        $query = Movement::query()
            ->with('account:id,currency')
            ->where('type', $type)
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end))
            ->when($this->contextId, fn ($q) => $q->where('financial_context_id', $this->contextId));

        if ($this->contextId) {
            $label = "{$noun} por categoría";
            $movements = $query->with('category')->get();
            $groupName = fn (Movement $movement): string => $movement->category?->name ?? 'Sin categoría';
        } else {
            $label = "{$noun} por contexto";
            $movements = $query->with('financialContext')->get();
            $groupName = fn (Movement $movement): string => $movement->financialContext?->name ?? 'Sin contexto';
        }

        $breakdown = $movements
            ->groupBy(fn (Movement $movement): string => $movement->currency())
            ->map(function (Collection $currencyMovements, string $currency) use ($groupName): array {
                $rows = $currencyMovements
                    ->groupBy($groupName)
                    ->map(fn (Collection $movements, string $name): array => ['name' => $name, 'total' => (float) $movements->sum('amount')])
                    ->sortByDesc('total')
                    ->take(5)
                    ->values();

                $max = $rows->max('total') ?: 1;

                return $rows
                    ->map(fn (array $row): array => [
                        ...$row,
                        'formattedTotal' => Money::format($row['total'], $currency),
                        'percent' => $max > 0 ? (int) round(($row['total'] / $max) * 100) : 0,
                    ])
                    ->all();
            })
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
