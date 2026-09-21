<?php

namespace Tequia\Finance\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;
use Tequia\Finance\Support\Money;
use UnitEnum;

class FinanceDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Resumen';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Resumen financiero';

    protected string $view = 'finance::filament.pages.finance-dashboard';

    public string $totalBalance = '$ 0,00';

    public string $monthIncome = '$ 0,00';

    public string $monthExpense = '$ 0,00';

    public string $monthNet = '$ 0,00';

    public bool $monthNetIsNegative = false;

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
     * @var array<int, array<string, mixed>>
     */
    public array $expenseByContext = [];

    public function mount(): void
    {
        $this->loadAccounts();
        $this->loadMonthSummary();
        $this->loadMonthlyCashflow();
        $this->loadRecentMovements();
        $this->loadExpenseByContext();
    }

    private function loadAccounts(): void
    {
        $accounts = Account::query()
            ->with(['movements', 'outgoingTransfers', 'incomingTransfers'])
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
            ])
            ->all();
    }

    private function loadMonthSummary(): void
    {
        [$start, $end] = [now()->startOfMonth(), now()->endOfMonth()];

        $income = (string) Movement::query()->where('type', MovementType::Income)->whereBetween('date', [$start, $end])->sum('amount');
        $expense = (string) Movement::query()->where('type', MovementType::Expense)->whereBetween('date', [$start, $end])->sum('amount');
        $net = bcsub($income, $expense, 2);

        $this->monthIncome = Money::format($income);
        $this->monthExpense = Money::format($expense);
        $this->monthNet = Money::format($net);
        $this->monthNetIsNegative = bccomp($net, '0', 2) < 0;
    }

    private function loadMonthlyCashflow(): void
    {
        $summaries = collect(range(5, 0))
            ->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth())
            ->map(function (Carbon $month): array {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $income = (float) Movement::query()->where('type', MovementType::Income)->whereBetween('date', [$start, $end])->sum('amount');
                $expense = (float) Movement::query()->where('type', MovementType::Expense)->whereBetween('date', [$start, $end])->sum('amount');

                return ['label' => ucfirst($month->translatedFormat('M Y')), 'income' => $income, 'expense' => $expense];
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
        $this->recentMovements = Movement::query()
            ->with(['account', 'fromAccount', 'toAccount', 'category'])
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
                'formattedAmount' => $movement->formattedAmount(),
                'isNegative' => $movement->type === MovementType::Expense
                    || ($movement->type === MovementType::Adjustment && $movement->amount < 0),
                'date' => $movement->date->format('d/m/Y'),
            ])
            ->all();
    }

    private function loadExpenseByContext(): void
    {
        [$start, $end] = [now()->startOfMonth(), now()->endOfMonth()];

        $rows = Movement::query()
            ->with('financialContext')
            ->where('type', MovementType::Expense)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn (Movement $movement): string => $movement->financialContext?->name ?? 'Sin contexto')
            ->map(fn ($movements, string $name): array => [
                'name' => $name,
                'total' => (float) $movements->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $max = $rows->max('total') ?: 1;

        $this->expenseByContext = $rows
            ->map(fn (array $row): array => [
                ...$row,
                'formattedTotal' => Money::format($row['total']),
                'percent' => $max > 0 ? (int) round(($row['total'] / $max) * 100) : 0,
            ])
            ->all();
    }
}
