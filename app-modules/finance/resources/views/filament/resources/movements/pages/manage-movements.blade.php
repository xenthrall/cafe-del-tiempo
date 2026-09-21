<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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

            <x-filament::button icon="heroicon-o-plus" wire:click="openCreateModal">
                Nuevo movimiento
            </x-filament::button>
        </div>

        @if (empty($movements))
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                No hay movimientos {{ $activeType === 'all' ? 'registrados' : 'de este tipo' }} todavía.
            </div>
        @else
            <div class="flex flex-col divide-y divide-gray-200 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                @foreach ($movements as $movement)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
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

                        <div class="flex shrink-0 items-center gap-3">
                            <span @class([
                                'font-semibold',
                                'text-danger-600 dark:text-danger-400' => $movement['isNegative'],
                                'text-success-600 dark:text-success-400' => ! $movement['isNegative'] && $movement['type'] !== 'transfer',
                                'text-gray-700 dark:text-gray-300' => $movement['type'] === 'transfer',
                            ])>
                                {{ $movement['isNegative'] ? '-' : '' }}{{ $movement['formattedAmount'] }}
                            </span>

                            <div class="flex items-center gap-1">
                                <x-filament::icon-button icon="heroicon-o-pencil-square" label="Editar" wire:click="openEditModal({{ $movement['id'] }})" />
                                <x-filament::icon-button icon="heroicon-o-trash" label="Eliminar" color="danger" wire:click="delete({{ $movement['id'] }})" wire:confirm="¿Eliminar este movimiento?" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament::modal
        id="movement-form-modal"
        width="lg"
        wire:submit.prevent="save"
    >
        <x-slot name="heading">
            {{ $editingId ? 'Editar movimiento' : 'Nuevo movimiento' }}
        </x-slot>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="type">
                        @foreach (\Tequia\Finance\Enums\MovementType::cases() as $movementType)
                            <option value="{{ $movementType->value }}">{{ $movementType->label() }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            @if ($type === 'transfer')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cuenta de origen</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="fromAccountId">
                                <option value="">Selecciona una cuenta</option>
                                @foreach ($accountOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('from_account_id')
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cuenta de destino</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="toAccountId">
                                <option value="">Selecciona una cuenta</option>
                                @foreach ($accountOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('to_account_id')
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cuenta</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="accountId">
                            <option value="">Selecciona una cuenta</option>
                            @foreach ($accountOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    @error('account_id')
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if (in_array($type, ['income', 'expense']))
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoría (opcional)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="categoryId">
                            <option value="">Sin categoría</option>
                            @foreach ($this->categoryOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            @endif

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Contexto financiero (opcional)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="financialContextId">
                        <option value="">Sin contexto</option>
                        @foreach ($contextOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Monto (COP)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model="amount" required />
                    </x-filament::input.wrapper>
                    @if ($type === 'adjustment')
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Usa un valor negativo para reducir el saldo.
                        </p>
                    @endif
                    @error('amount')
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model="date" required />
                    </x-filament::input.wrapper>
                    @error('date')
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (opcional)</label>
                <x-filament::input.wrapper>
                    <textarea
                        wire:model="description"
                        rows="2"
                        class="fi-input block w-full border-none bg-transparent p-0 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500"
                    ></textarea>
                </x-filament::input.wrapper>
            </div>

            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
