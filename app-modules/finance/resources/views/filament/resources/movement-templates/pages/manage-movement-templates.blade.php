<x-filament-panels::page>
    <div class="flex flex-col gap-5">
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-bolt" class="h-5 w-5" />
            <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Frecuentes</h1>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Movimientos que registras seguido (arriendo, suscripciones, salario…). Define la plantilla una vez y luego solo confirma la fecha para registrarla.
        </p>

        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-filament::tabs contained>
                    <x-filament::tabs.item :active="$typeFilter === 'all'" wire:click="setTypeFilter('all')">
                        Todas
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$typeFilter === 'expense'" icon="heroicon-o-arrow-trending-down" wire:click="setTypeFilter('expense')">
                        Gastos
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$typeFilter === 'income'" icon="heroicon-o-arrow-trending-up" wire:click="setTypeFilter('income')">
                        Ingresos
                    </x-filament::tabs.item>
                </x-filament::tabs>

                <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                    {{ ($this->manageMovementTemplateAction)([]) }}
                </div>
            </div>

            @if ($hasAnyTemplates)
                <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar plantilla por nombre…"
                    />
                </x-filament::input.wrapper>
            @endif
        </div>

        @if (empty($templates))
            <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center dark:border-white/10">
                @if (! $hasAnyTemplates)
                    <x-filament::icon icon="heroicon-o-bolt" class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Todavía no tienes movimientos frecuentes.<br class="sm:hidden" />
                        Crea el primero arriba para registrar en un tap.
                    </p>
                @else
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No encontramos plantillas que coincidan.
                    </p>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($templates as $template)
                    <div @class([
                        'flex flex-col gap-3 rounded-xl border border-gray-200 p-4 dark:border-white/10',
                        'opacity-60' => ! $template['isActive'],
                    ])>
                        <div class="flex items-center justify-between gap-2">
                            <x-filament::badge :color="$template['typeColor']" :icon="$template['typeIcon']" size="sm">
                                {{ $template['typeLabel'] }}
                            </x-filament::badge>

                            <x-filament-actions::group
                                :actions="[
                                    ($this->manageMovementTemplateAction)(['template' => $template['id']]),
                                    ($this->toggleTemplateActiveAction)(['template' => $template['id'], 'active' => $template['isActive']]),
                                    ($this->deleteMovementTemplateAction)(['template' => $template['id']]),
                                ]"
                                icon="heroicon-m-ellipsis-vertical"
                                color="gray"
                                label="Más acciones"
                            />
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-medium text-gray-950 dark:text-white">
                                {{ $template['name'] }}
                            </p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $template['accountName'] }}
                                @if ($template['categoryName'])
                                    · {{ $template['categoryName'] }}
                                @endif
                                @if ($template['contextName'])
                                    · {{ $template['contextName'] }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <p class="text-2xl font-semibold text-gray-950 dark:text-white">
                                {{ $template['formattedAmount'] }}
                            </p>

                            @unless ($template['isActive'])
                                <x-filament::badge color="warning" size="sm">Archivada</x-filament::badge>
                            @endunless
                        </div>

                        {{ ($this->manageMovementAction)(['template' => $template['id']])
                            ->label('Registrar')
                            ->icon('heroicon-o-check-circle')
                            ->modalHeading('Registrar: '.$template['name'])
                            ->button()
                            ->size(\Filament\Support\Enums\Size::Large)
                            ->extraAttributes(['class' => 'w-full justify-center']) }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
