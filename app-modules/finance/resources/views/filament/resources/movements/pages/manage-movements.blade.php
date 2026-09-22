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

        <div x-data="{ open: false }" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
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
                <div class="w-full sm:w-44">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="periodPreset">
                            <option value="">Todo el historial</option>
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

                <div class="w-full sm:w-48">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="categoryId">
                            <option value="">Todas las categorías</option>
                            @foreach ($this->categoryOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="flex items-center justify-end gap-1">
                {{ ($this->generateMovementsReportAction)([]) }}

                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-white/10"></div>

                <x-filament::icon-button
                    icon="heroicon-o-rectangle-stack"
                    label="Vista de tarjetas"
                    :color="$viewMode === 'cards' ? 'primary' : 'gray'"
                    wire:click="setViewMode('cards')"
                />
                <x-filament::icon-button
                    icon="heroicon-o-table-cells"
                    label="Vista de tabla"
                    :color="$viewMode === 'columns' ? 'primary' : 'gray'"
                    wire:click="setViewMode('columns')"
                />
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
