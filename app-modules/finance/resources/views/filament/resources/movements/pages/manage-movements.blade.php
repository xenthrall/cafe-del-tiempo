<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
            <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Movimientos</h1>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:overflow-visible sm:px-0">
                <x-filament::tabs contained>
                    <x-filament::tabs.item :active="$activeType === 'all'" wire:click="setActiveType('all')">
                        Todos
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$activeType === 'income'" icon="heroicon-o-arrow-trending-up" wire:click="setActiveType('income')">
                        Ingresos
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$activeType === 'expense'" icon="heroicon-o-arrow-trending-down" wire:click="setActiveType('expense')">
                        Gastos
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$activeType === 'transfer'" icon="heroicon-o-arrows-right-left" wire:click="setActiveType('transfer')">
                        Transferencias
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$activeType === 'adjustment'" icon="heroicon-o-adjustments-horizontal" wire:click="setActiveType('adjustment')">
                        Ajustes
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>

            <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                {{ ($this->manageMovementAction)([
                    'type' => $activeType === 'all' ? null : $activeType,
                ]) }}
            </div>
        </div>

        @if (empty($movements))
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                No hay movimientos {{ $activeType === 'all' ? 'registrados' : 'de este tipo' }} todavía.
            </div>
        @else
            <div class="flex flex-col divide-y divide-gray-200 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                @foreach ($movements as $movement)
                    <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-filament::badge :color="$movement['typeColor']" :icon="$movement['typeIcon']">
                                {{ $movement['typeLabel'] }}
                            </x-filament::badge>

                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-950 dark:text-white">
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
                                    @if ($movement['description'])
                                        · {{ $movement['description'] }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:shrink-0 sm:justify-end">
                            <span @class([
                                'font-semibold',
                                'text-danger-600 dark:text-danger-400' => $movement['isNegative'],
                                'text-success-600 dark:text-success-400' => ! $movement['isNegative'] && $movement['type'] !== 'transfer',
                                'text-gray-700 dark:text-gray-300' => $movement['type'] === 'transfer',
                            ])>
                                {{ $movement['isNegative'] ? '-' : '' }}{{ $movement['formattedAmount'] }}
                            </span>

                            <div class="flex items-center gap-1">
                                {{ ($this->manageMovementAction)(['movement' => $movement['id']])->iconButton() }}
                                {{ ($this->deleteMovementAction)(['movement' => $movement['id']]) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
