<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-o-users" class="h-5 w-5" />
                <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Usuarios</h1>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
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

                <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                    {{ ($this->manageUserAction)([]) }}
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
