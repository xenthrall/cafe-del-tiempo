<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Stat cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Saldo total</p>
                <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $totalBalance }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Ingresos del mes</p>
                <p class="mt-1 text-2xl font-semibold text-success-600 dark:text-success-400">{{ $monthIncome }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Gastos del mes</p>
                <p class="mt-1 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ $monthExpense }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Neto del mes</p>
                <p @class([
                    'mt-1 text-2xl font-semibold',
                    'text-danger-600 dark:text-danger-400' => $monthNetIsNegative,
                    'text-gray-950 dark:text-white' => ! $monthNetIsNegative,
                ])>
                    {{ $monthNet }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Cashflow chart --}}
            <div class="xl:col-span-2">
                <x-filament::section heading="Ingresos vs. gastos (últimos 6 meses)">
                    <div class="flex items-end gap-4 overflow-x-auto pb-2">
                        @foreach ($monthlyCashflow as $month)
                            <div class="flex min-w-[3.5rem] flex-1 flex-col items-center gap-2">
                                <div class="flex h-32 w-full items-end justify-center gap-1">
                                    <div
                                        class="w-3 rounded-t bg-success-500/80 dark:bg-success-400/80"
                                        style="height: {{ max($month['incomePercent'], 2) }}%"
                                        title="Ingresos: {{ $month['income'] }}"
                                    ></div>
                                    <div
                                        class="w-3 rounded-t bg-danger-500/80 dark:bg-danger-400/80"
                                        style="height: {{ max($month['expensePercent'], 2) }}%"
                                        title="Gastos: {{ $month['expense'] }}"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-success-500"></span> Ingresos
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-danger-500"></span> Gastos
                        </span>
                    </div>
                </x-filament::section>
            </div>

            {{-- Accounts --}}
            <x-filament::section heading="Tus cuentas">
                @if (empty($accounts))
                    <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no tienes cuentas registradas.</p>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach ($accounts as $account)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <x-filament::icon :icon="$account['icon']" class="h-4 w-4 shrink-0 text-gray-400" />
                                    <span class="truncate text-sm text-gray-700 dark:text-gray-300">{{ $account['name'] }}</span>
                                </div>
                                <span @class([
                                    'shrink-0 text-sm font-medium',
                                    'text-danger-600 dark:text-danger-400' => $account['isNegative'],
                                    'text-gray-950 dark:text-white' => ! $account['isNegative'],
                                ])>
                                    {{ $account['formattedBalance'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Recent movements --}}
            <div class="xl:col-span-2">
                <x-filament::section heading="Movimientos recientes">
                    @if (empty($recentMovements))
                        <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no hay movimientos registrados.</p>
                    @else
                        <div class="flex flex-col divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($recentMovements as $movement)
                                <div class="flex items-center justify-between gap-3 py-2.5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-filament::badge :color="$movement['typeColor']" :icon="$movement['typeIcon']" size="sm">
                                            {{ $movement['typeLabel'] }}
                                        </x-filament::badge>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm text-gray-950 dark:text-white">
                                                {{ $movement['accountsLabel'] }}
                                                @if ($movement['categoryName'])
                                                    <span class="text-gray-400">· {{ $movement['categoryName'] }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement['date'] }}</p>
                                        </div>
                                    </div>
                                    <span @class([
                                        'shrink-0 text-sm font-semibold',
                                        'text-danger-600 dark:text-danger-400' => $movement['isNegative'],
                                        'text-success-600 dark:text-success-400' => ! $movement['isNegative'] && $movement['typeLabel'] !== 'Transferencia',
                                    ])>
                                        {{ $movement['isNegative'] ? '-' : '' }}{{ $movement['formattedAmount'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- Expenses by context --}}
            <x-filament::section heading="Gastos del mes por contexto">
                @if (empty($expenseByContext))
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay gastos registrados este mes.</p>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach ($expenseByContext as $row)
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="truncate text-gray-700 dark:text-gray-300">{{ $row['name'] }}</span>
                                    <span class="shrink-0 font-medium text-gray-950 dark:text-white">{{ $row['formattedTotal'] }}</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-danger-500/80" style="width: {{ max($row['percent'], 4) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
