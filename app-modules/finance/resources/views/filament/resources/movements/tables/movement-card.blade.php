@php
    /** @var \Tequia\Finance\Models\Movement $record */
    $record = $getRecord();

    $isNegative = $record->type->value === 'expense'
        || ($record->type->value === 'adjustment' && $record->amount < 0);
@endphp

<div class="flex flex-col gap-2 py-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
    <div class="flex items-center gap-3 min-w-0">
        <x-filament::badge :color="$record->type->color()" :icon="$record->type->icon()">
            {{ $record->type->label() }}
        </x-filament::badge>

        <div class="min-w-0">
            <p class="truncate font-medium text-gray-950 dark:text-white">
                {{ $record->accountsLabel() }}
                @if ($record->category?->name)
                    <span class="text-gray-400">· {{ $record->category->name }}</span>
                @endif
            </p>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                {{ $record->date->format('d/m/Y') }}
                @if ($record->financialContext?->name)
                    · {{ $record->financialContext->name }}
                @endif
                @if ($record->description)
                    · {{ $record->description }}
                @endif
            </p>
        </div>
    </div>

    <span @class([
        'font-semibold sm:shrink-0',
        'text-danger-600 dark:text-danger-400' => $isNegative,
        'text-success-600 dark:text-success-400' => ! $isNegative && $record->type->value !== 'transfer',
        'text-gray-700 dark:text-gray-300' => $record->type->value === 'transfer',
    ])>
        {{ $isNegative ? '-' : '' }}{{ $record->formattedAmount() }}
    </span>
</div>
