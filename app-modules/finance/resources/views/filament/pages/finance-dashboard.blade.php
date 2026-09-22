<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-o-chart-pie" class="h-5 w-5" />
                <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Resumen financiero</h1>
            </div>

            <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                {{ ($this->manageMovementAction)([]) }}
            </div>
        </div>

        {{-- Filtros: se aplican a todo el resumen de esta página --}}
        <div x-data="{ open: false }" class="flex flex-col gap-3 rounded-xl border border-gray-200 p-3 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
            <button
                type="button"
                x-on:click="open = ! open"
                class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 lg:hidden"
            >
                <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
                Filtros{{ $this->activeFilterCount() ? " ({$this->activeFilterCount()})" : '' }}
                <x-filament::icon icon="heroicon-o-chevron-down" x-bind:class="open && 'rotate-180'" class="h-3.5 w-3.5 transition-transform" />
            </button>

            <div :class="open ? 'flex' : 'hidden'" class="flex-col flex-wrap items-center gap-2 lg:flex lg:flex-row">
                <span class="hidden items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 lg:flex">
                    <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
                    Filtros
                </span>

                <div class="w-full sm:w-44">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="periodPreset">
                            <option value="all">Todo el historial</option>
                            <option value="week">Esta semana</option>
                            <option value="month">Este mes</option>
                            <option value="year">Este año</option>
                            <option value="custom">Rango personalizado</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                @if ($periodPreset === 'custom')
                    <div class="w-[calc(50%-0.25rem)] sm:w-36">
                        <x-filament::input.wrapper>
                            <x-filament::input type="date" wire:model.live="periodFrom" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="w-[calc(50%-0.25rem)] sm:w-36">
                        <x-filament::input.wrapper>
                            <x-filament::input type="date" wire:model.live="periodUntil" />
                        </x-filament::input.wrapper>
                    </div>
                @endif

                <div class="w-full sm:w-48">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="contextId">
                            <option value="">Todos los contextos</option>
                            @foreach ($this->contextOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <a href="{{ $this->movementsUrl() }}" class="flex shrink-0 items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                Gestionar movimientos
                <x-filament::icon icon="heroicon-o-arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-4 w-4" />
                    Saldo total
                </p>
                <div class="mt-1 flex flex-col">
                    @foreach ($totalBalances as $balance)
                        <p class="text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ $balance['formatted'] }}
                            @if (count($totalBalances) > 1)
                                <span class="text-xs font-normal text-gray-400">{{ $balance['currency'] }}</span>
                            @endif
                        </p>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-400">De todas tus cuentas, hoy</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-4 w-4" />
                    Ingresos
                </p>
                <div class="mt-1 flex flex-col">
                    @foreach ($periodIncomes as $income)
                        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                            {{ $income['formatted'] }}
                            @if (count($periodIncomes) > 1)
                                <span class="text-xs font-normal text-gray-400">{{ $income['currency'] }}</span>
                            @endif
                        </p>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-400">
                    {{ match ($periodPreset) {
                        'week' => 'Esta semana',
                        'month' => 'Este mes',
                        'year' => 'Este año',
                        'custom' => 'En el rango elegido',
                        default => 'En todo el historial',
                    } }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-o-arrow-trending-down" class="h-4 w-4" />
                    Gastos
                </p>
                <div class="mt-1 flex flex-col">
                    @foreach ($periodExpenses as $expense)
                        <p class="text-2xl font-semibold text-danger-600 dark:text-danger-400">
                            {{ $expense['formatted'] }}
                            @if (count($periodExpenses) > 1)
                                <span class="text-xs font-normal text-gray-400">{{ $expense['currency'] }}</span>
                            @endif
                        </p>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-400">
                    {{ match ($periodPreset) {
                        'week' => 'Esta semana',
                        'month' => 'Este mes',
                        'year' => 'Este año',
                        'custom' => 'En el rango elegido',
                        default => 'En todo el historial',
                    } }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-o-scale" class="h-4 w-4" />
                    Neto
                </p>
                <div class="mt-1 flex flex-col">
                    @foreach ($periodNets as $net)
                        <p @class([
                            'text-2xl font-semibold',
                            'text-danger-600 dark:text-danger-400' => $net['isNegative'],
                            'text-gray-950 dark:text-white' => ! $net['isNegative'],
                        ])>
                            {{ $net['formatted'] }}
                            @if (count($periodNets) > 1)
                                <span class="text-xs font-normal text-gray-400">{{ $net['currency'] }}</span>
                            @endif
                        </p>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-400">Ingresos menos gastos</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Cashflow chart: un bloque por moneda en uso (casi siempre una sola) --}}
            <div class="xl:col-span-2 flex flex-col gap-6">
                @foreach ($monthlyCashflow as $currency => $months)
                    <x-filament::section :heading="count($monthlyCashflow) > 1 ? 'Ingresos vs. gastos (' . $currency . ', últimos 6 meses)' : 'Ingresos vs. gastos (últimos 6 meses)'">
                        <div class="flex items-end gap-4 overflow-x-auto pb-2">
                            @foreach ($months as $month)
                                <div class="flex min-w-[3.5rem] flex-1 flex-col items-center gap-2">
                                    <div class="flex h-32 w-full items-end justify-center gap-1">
                                        <div
                                            class="w-3 rounded-t bg-success-500/80 dark:bg-success-400/80"
                                            style="height: {{ max($month['incomePercent'], 2) }}%"
                                            title="Ingresos: {{ $month['formattedIncome'] }}"
                                        ></div>
                                        <div
                                            class="w-3 rounded-t bg-danger-500/80 dark:bg-danger-400/80"
                                            style="height: {{ max($month['expensePercent'], 2) }}%"
                                            title="Gastos: {{ $month['formattedExpense'] }}"
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
                            @if ($contextId)
                                <span class="text-gray-400">· Solo {{ $this->contextOptions()->get($contextId) }}</span>
                            @endif
                        </div>
                    </x-filament::section>
                @endforeach
            </div>

            {{-- Accounts --}}
            <x-filament::section heading="Tus cuentas">
                <x-slot name="afterHeader">
                    <a href="{{ \Tequia\Finance\Filament\Resources\Accounts\AccountResource::getUrl() }}" class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        Gestionar
                    </a>
                </x-slot>

                @if (empty($accounts))
                    <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no tienes cuentas registradas.</p>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach ($accounts as $account)
                            <div @class(['flex items-center justify-between gap-2', 'opacity-60' => ! $account['isActive']])>
                                <div class="flex items-center gap-2 min-w-0">
                                    <x-filament::icon :icon="$account['icon']" class="h-4 w-4 shrink-0 text-gray-400" />
                                    <span class="truncate text-sm text-gray-700 dark:text-gray-300">
                                        {{ $account['name'] }}
                                        @unless ($account['isActive'])
                                            <span class="text-xs text-warning-600 dark:text-warning-400">(archivada)</span>
                                        @endunless
                                    </span>
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

        {{-- Recent movements --}}
        <x-filament::section heading="Movimientos recientes">
            <x-slot name="afterHeader">
                <a href="{{ $this->movementsUrl() }}" class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    Ver todos
                </a>
            </x-slot>

            @if (empty($recentMovements))
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay movimientos con los filtros aplicados.</p>
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
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $movement['date'] }}
                                        @if ($movement['contextName'])
                                            · {{ $movement['contextName'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <span @class([
                                    'text-sm font-semibold',
                                    'text-danger-600 dark:text-danger-400' => $movement['isNegative'],
                                    'text-success-600 dark:text-success-400' => ! $movement['isNegative'] && $movement['typeLabel'] !== 'Transferencia',
                                ])>
                                    {{ $movement['isNegative'] ? '-' : '' }}{{ $movement['formattedAmount'] }}
                                </span>

                                <div class="flex items-center gap-0.5">
                                    {{ ($this->manageMovementAction)(['movement' => $movement['id']])->iconButton() }}
                                    {{ ($this->deleteMovementAction)(['movement' => $movement['id']]) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Expense breakdown: by context, or by category when a context is already selected — un bloque por moneda --}}
            <x-filament::section :heading="$expenseBreakdownLabel">
                @if (empty($expenseBreakdown))
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay gastos con los filtros aplicados.</p>
                @else
                    <div class="flex flex-col gap-5">
                        @foreach ($expenseBreakdown as $currency => $rows)
                            <div class="flex flex-col gap-3">
                                @if (count($expenseBreakdown) > 1)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $currency }}</span>
                                @endif
                                @foreach ($rows as $row)
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
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            {{-- Income breakdown: by context, or by category when a context is already selected — un bloque por moneda --}}
            <x-filament::section :heading="$incomeBreakdownLabel">
                @if (empty($incomeBreakdown))
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay ingresos con los filtros aplicados.</p>
                @else
                    <div class="flex flex-col gap-5">
                        @foreach ($incomeBreakdown as $currency => $rows)
                            <div class="flex flex-col gap-3">
                                @if (count($incomeBreakdown) > 1)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $currency }}</span>
                                @endif
                                @foreach ($rows as $row)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="truncate text-gray-700 dark:text-gray-300">{{ $row['name'] }}</span>
                                            <span class="shrink-0 font-medium text-gray-950 dark:text-white">{{ $row['formattedTotal'] }}</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                            <div class="h-full rounded-full bg-success-500/80" style="width: {{ max($row['percent'], 4) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Glosario: colapsado por defecto para no saturar el dashboard --}}
        <x-filament::section
            heading="Explorar conceptos"
            description="Qué significan los términos que usa este resumen"
            icon="heroicon-o-book-open"
            collapsible
            collapsed
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($this->glossary() as $entry)
                    <div class="flex flex-col gap-0.5">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $entry['term'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $entry['definition'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
