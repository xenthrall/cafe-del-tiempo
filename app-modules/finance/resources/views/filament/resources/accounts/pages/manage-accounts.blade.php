<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-wallet" class="h-5 w-5" />
            <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Cuentas</h1>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Efectivo, cuentas bancarias, billeteras digitales y tarjetas de crédito. El saldo se calcula a partir del saldo inicial y los movimientos registrados.
            </p>

            <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                {{ ($this->manageAccountAction)([]) }}
            </div>
        </div>

        @if (empty($accounts))
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                Todavía no tienes cuentas registradas. Crea la primera arriba.
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($accounts as $account)
                    <div class="flex flex-col gap-3 rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon :icon="$account['typeIcon']" class="h-5 w-5 shrink-0 text-gray-400" />
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-950 dark:text-white">{{ $account['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $account['typeLabel'] }}</p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                {{ ($this->manageAccountAction)(['account' => $account['id']])->iconButton() }}
                                {{ ($this->deleteAccountAction)(['account' => $account['id']]) }}
                            </div>
                        </div>

                        <p @class([
                            'text-2xl font-semibold',
                            'text-danger-600 dark:text-danger-400' => $account['balance'] < 0,
                            'text-gray-950 dark:text-white' => $account['balance'] >= 0,
                        ])>
                            {{ $account['formattedBalance'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
