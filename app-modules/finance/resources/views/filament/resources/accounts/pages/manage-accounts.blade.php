<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Efectivo, cuentas bancarias, billeteras digitales y tarjetas de crédito. El saldo se calcula a partir del saldo inicial y los movimientos registrados.
            </p>

            <x-filament::button
                icon="heroicon-o-plus"
                wire:click="openCreateModal"
            >
                Nueva cuenta
            </x-filament::button>
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
                                <x-filament::icon-button icon="heroicon-o-pencil-square" label="Editar" wire:click="openEditModal({{ $account['id'] }})" />
                                <x-filament::icon-button icon="heroicon-o-trash" label="Eliminar" color="danger" wire:click="delete({{ $account['id'] }})" wire:confirm="¿Eliminar esta cuenta?" />
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

    <x-filament::modal
        id="account-form-modal"
        width="md"
        wire:submit.prevent="save"
    >
        <x-slot name="heading">
            {{ $editingId ? 'Editar cuenta' : 'Nueva cuenta' }}
        </x-slot>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="name" placeholder="Bancolombia ahorros, Nequi, Efectivo…" required />
                </x-filament::input.wrapper>
                @error('name')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="type">
                        @foreach (\Tequia\Finance\Enums\AccountType::cases() as $accountType)
                            <option value="{{ $accountType->value }}">{{ $accountType->label() }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Saldo inicial (COP)</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="number" step="0.01" wire:model="openingBalance" />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Puede ser negativo (por ejemplo, una tarjeta de crédito con saldo pendiente).
                </p>
                @error('opening_balance')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
