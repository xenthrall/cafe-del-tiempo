<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Los contextos agrupan movimientos por ámbito o actividad (personal, un vehículo, un trabajo…), para poder analizarlos por separado.
            </p>

            <x-filament::button
                icon="heroicon-o-plus"
                wire:click="openCreateModal"
            >
                Nuevo contexto
            </x-filament::button>
        </div>

        @if (empty($contexts))
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                Todavía no tienes contextos financieros. Crea el primero arriba.
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($contexts as $context)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-filament::icon
                                icon="heroicon-o-tag"
                                class="h-5 w-5 shrink-0 text-gray-400"
                            />

                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-950 dark:text-white">
                                    {{ $context['name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $context['movementsCount'] }} {{ $context['movementsCount'] === 1 ? 'movimiento' : 'movimientos' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <x-filament::icon-button
                                icon="heroicon-o-pencil-square"
                                label="Editar"
                                wire:click="openEditModal({{ $context['id'] }})"
                            />
                            <x-filament::icon-button
                                icon="heroicon-o-trash"
                                label="Eliminar"
                                color="danger"
                                wire:click="delete({{ $context['id'] }})"
                                wire:confirm="¿Eliminar este contexto? Los movimientos que lo usan quedarán sin contexto."
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament::modal
        id="financial-context-form-modal"
        width="md"
        wire:submit.prevent="save"
    >
        <x-slot name="heading">
            {{ $editingId ? 'Editar contexto' : 'Nuevo contexto' }}
        </x-slot>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="name"
                        placeholder="Personal, Vehículo Turbo, Trabajo…"
                        required
                    />
                </x-filament::input.wrapper>
                @error('name')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
