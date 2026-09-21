<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-tag" class="h-5 w-5" />
            <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Contextos</h1>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            {{-- Panel maestro: contextos --}}
            <div class="flex w-full flex-col gap-3 lg:w-72 lg:shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Contextos</h2>
                    {{ ($this->manageContextAction)([])->iconButton() }}
                </div>

                <div class="flex flex-col gap-1.5">
                    <button
                        type="button"
                        wire:click="selectContext(null)"
                        @class([
                            'flex items-center gap-2 rounded-lg border px-3 py-2 text-left text-sm transition-colors',
                            'border-primary-600 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-400/10 dark:text-primary-400' => $selectedContextId === null,
                            'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5' => $selectedContextId !== null,
                        ])
                    >
                        <x-filament::icon icon="heroicon-o-inbox" class="h-4 w-4 shrink-0" />
                        <span class="truncate font-medium">General</span>
                        <span class="ml-auto shrink-0 text-xs text-gray-400">sin contexto</span>
                    </button>

                    @forelse ($contexts as $context)
                        <div
                            @class([
                                'group flex items-center gap-1 rounded-lg border px-2 py-2 transition-colors',
                                'border-primary-600 bg-primary-50 dark:border-primary-400 dark:bg-primary-400/10' => $selectedContextId === $context['id'],
                                'border-gray-200 dark:border-white/10' => $selectedContextId !== $context['id'],
                                'opacity-60' => ! $context['isActive'],
                            ])
                        >
                            <button
                                type="button"
                                wire:click="selectContext({{ $context['id'] }})"
                                class="flex min-w-0 flex-1 items-center gap-2 px-1 py-0.5 text-left"
                            >
                                <x-filament::icon icon="heroicon-o-tag" class="h-4 w-4 shrink-0 text-gray-400" />
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $context['name'] }}
                                    </span>
                                    <span class="block truncate text-xs text-gray-400">
                                        {{ $context['categoriesCount'] }} {{ $context['categoriesCount'] === 1 ? 'categoría' : 'categorías' }}
                                        · {{ $context['movementsCount'] }} {{ $context['movementsCount'] === 1 ? 'movimiento' : 'movimientos' }}
                                        @unless ($context['isActive'])
                                            · <span class="text-warning-600 dark:text-warning-400">Archivado</span>
                                        @endunless
                                    </span>
                                </span>
                            </button>

                            <div class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100">
                                <x-filament::icon-button
                                    :icon="$context['isActive'] ? 'heroicon-o-archive-box' : 'heroicon-o-archive-box-x-mark'"
                                    :label="$context['isActive'] ? 'Archivar' : 'Reactivar'"
                                    wire:click="toggleContextActive({{ $context['id'] }})"
                                />
                                {{ ($this->manageContextAction)(['context' => $context['id']])->iconButton() }}
                                {{ ($this->deleteContextAction)(['context' => $context['id']]) }}
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-4 text-center text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                            Todavía no tienes contextos financieros.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Panel de detalle: categorías del contexto seleccionado --}}
            <div class="flex min-w-0 flex-1 flex-col gap-4">
                <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                    Categorías
                    <span class="font-normal text-gray-400">
                        — {{ $selectedContextId ? (collect($contexts)->firstWhere('id', $selectedContextId)['name'] ?? '') : 'General (sin contexto)' }}
                    </span>
                </h2>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <x-filament::tabs contained>
                        <x-filament::tabs.item :active="$categoryType === 'all'" wire:click="setCategoryType('all')">
                            Todos
                        </x-filament::tabs.item>
                        <x-filament::tabs.item :active="$categoryType === 'expense'" icon="heroicon-o-arrow-trending-down" wire:click="setCategoryType('expense')">
                            Gastos
                        </x-filament::tabs.item>
                        <x-filament::tabs.item :active="$categoryType === 'income'" icon="heroicon-o-arrow-trending-up" wire:click="setCategoryType('income')">
                            Ingresos
                        </x-filament::tabs.item>
                    </x-filament::tabs>

                    <div class="w-full sm:w-auto [&>button]:w-full sm:[&>button]:w-auto">
                        {{ ($this->manageCategoryAction)([
                            'financial_context_id' => $selectedContextId,
                            'type' => $categoryType === 'all' ? 'expense' : $categoryType,
                        ]) }}
                    </div>
                </div>

                @if (empty($categories))
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        @if ($categoryType === 'all')
                            Todavía no hay categorías en este contexto.
                        @else
                            Todavía no hay categorías de {{ $categoryType === 'expense' ? 'gasto' : 'ingreso' }} en este contexto.
                        @endif
                    </div>
                @else
                    <div class="flex flex-col divide-y divide-gray-200 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        @foreach ($categories as $category)
                            <div @class([
                                'flex items-center justify-between gap-3 px-4 py-3',
                                'opacity-60' => ! $category['isActive'],
                            ])>
                                <div class="flex items-center gap-2 min-w-0">
                                    <x-filament::icon icon="heroicon-o-tag" class="h-4 w-4 shrink-0 text-gray-400" />
                                    <span class="truncate font-medium text-gray-950 dark:text-white">{{ $category['name'] }}</span>
                                    @if ($categoryType === 'all')
                                        <x-filament::badge :color="$category['type'] === 'expense' ? 'danger' : 'success'" size="sm">
                                            {{ $category['typeLabel'] }}
                                        </x-filament::badge>
                                    @endif
                                    <span class="shrink-0 text-xs text-gray-400">({{ $category['movementsCount'] }})</span>
                                    @unless ($category['isActive'])
                                        <span class="shrink-0 text-xs text-warning-600 dark:text-warning-400">Archivada</span>
                                    @endunless
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <x-filament::icon-button
                                        :icon="$category['isActive'] ? 'heroicon-o-archive-box' : 'heroicon-o-archive-box-x-mark'"
                                        :label="$category['isActive'] ? 'Archivar' : 'Reactivar'"
                                        wire:click="toggleCategoryActive({{ $category['id'] }})"
                                    />
                                    {{ ($this->manageCategoryAction)([
                                        'category' => $category['id'],
                                        'financial_context_id' => $selectedContextId,
                                    ])->iconButton() }}
                                    {{ ($this->deleteCategoryAction)(['category' => $category['id']]) }}
                                </div>
                            </div>

                            @foreach ($category['children'] as $child)
                                <div @class([
                                    'flex items-center justify-between gap-3 bg-gray-50/50 px-4 py-3 pl-10 dark:bg-white/[0.02]',
                                    'opacity-60' => ! $child['isActive'],
                                ])>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <x-filament::icon icon="heroicon-o-arrow-turn-down-right" class="h-4 w-4 shrink-0 text-gray-300" />
                                        <span class="truncate text-gray-700 dark:text-gray-300">{{ $child['name'] }}</span>
                                        @if ($categoryType === 'all')
                                            <x-filament::badge :color="$child['type'] === 'expense' ? 'danger' : 'success'" size="sm">
                                                {{ $child['typeLabel'] }}
                                            </x-filament::badge>
                                        @endif
                                        <span class="shrink-0 text-xs text-gray-400">({{ $child['movementsCount'] }})</span>
                                        @unless ($child['isActive'])
                                            <span class="shrink-0 text-xs text-warning-600 dark:text-warning-400">Archivada</span>
                                        @endunless
                                    </div>

                                    <div class="flex shrink-0 items-center gap-1">
                                        <x-filament::icon-button
                                            :icon="$child['isActive'] ? 'heroicon-o-archive-box' : 'heroicon-o-archive-box-x-mark'"
                                            :label="$child['isActive'] ? 'Archivar' : 'Reactivar'"
                                            wire:click="toggleCategoryActive({{ $child['id'] }})"
                                        />
                                        {{ ($this->manageCategoryAction)([
                                            'category' => $child['id'],
                                            'financial_context_id' => $selectedContextId,
                                        ])->iconButton() }}
                                        {{ ($this->deleteCategoryAction)(['category' => $child['id']]) }}
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
