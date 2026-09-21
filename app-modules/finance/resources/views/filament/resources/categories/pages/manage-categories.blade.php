<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-filament::tabs contained>
                <x-filament::tabs.item
                    :active="$activeType === 'expense'"
                    wire:click="setActiveType('expense')"
                    icon="heroicon-o-arrow-trending-down"
                >
                    Gastos
                </x-filament::tabs.item>
                <x-filament::tabs.item
                    :active="$activeType === 'income'"
                    wire:click="setActiveType('income')"
                    icon="heroicon-o-arrow-trending-up"
                >
                    Ingresos
                </x-filament::tabs.item>
            </x-filament::tabs>

            <x-filament::button
                icon="heroicon-o-plus"
                wire:click="openCreateModal"
            >
                Nueva categoría
            </x-filament::button>
        </div>

        @if (empty($categories))
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                Todavía no tienes categorías de {{ $activeType === 'expense' ? 'gasto' : 'ingreso' }}. Crea la primera arriba.
            </div>
        @else
            <div class="flex flex-col divide-y divide-gray-200 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                @foreach ($categories as $category)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <x-filament::icon icon="heroicon-o-tag" class="h-4 w-4 shrink-0 text-gray-400" />
                            <span class="truncate font-medium text-gray-950 dark:text-white">{{ $category['name'] }}</span>
                            <span class="shrink-0 text-xs text-gray-400">({{ $category['movementsCount'] }})</span>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <x-filament::icon-button icon="heroicon-o-pencil-square" label="Editar" wire:click="openEditModal({{ $category['id'] }})" />
                            <x-filament::icon-button icon="heroicon-o-trash" label="Eliminar" color="danger" wire:click="delete({{ $category['id'] }})" wire:confirm="¿Eliminar esta categoría?" />
                        </div>
                    </div>

                    @foreach ($category['children'] as $child)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 pl-10 bg-gray-50/50 dark:bg-white/[0.02]">
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon icon="heroicon-o-arrow-turn-down-right" class="h-4 w-4 shrink-0 text-gray-300" />
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ $child['name'] }}</span>
                                <span class="shrink-0 text-xs text-gray-400">({{ $child['movementsCount'] }})</span>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <x-filament::icon-button icon="heroicon-o-pencil-square" label="Editar" wire:click="openEditModal({{ $child['id'] }})" />
                                <x-filament::icon-button icon="heroicon-o-trash" label="Eliminar" color="danger" wire:click="delete({{ $child['id'] }})" wire:confirm="¿Eliminar esta categoría?" />
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>

    <x-filament::modal
        id="category-form-modal"
        width="md"
        wire:submit.prevent="save"
    >
        <x-slot name="heading">
            {{ $editingId ? 'Editar categoría' : 'Nueva categoría' }} de {{ $type === 'expense' ? 'gasto' : 'ingreso' }}
        </x-slot>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="name" placeholder="Transporte, Vivienda…" required />
                </x-filament::input.wrapper>
                @error('name')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoría padre (opcional)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="parentId">
                        <option value="">Ninguna (categoría de primer nivel)</option>
                        @foreach ($parentOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                @error('parent_id')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
