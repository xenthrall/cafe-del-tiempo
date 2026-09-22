@php
    /** @var \App\Models\User $record */
    $record = $getRecord();
@endphp

<div class="flex items-center justify-between gap-4 py-1">
    <div class="flex min-w-0 items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            {{ \Illuminate\Support\Str::of($record->name)->substr(0, 1)->upper() }}
        </div>

        <div class="min-w-0">
            <p class="flex items-center gap-2 truncate font-medium text-gray-950 dark:text-white">
                <span class="truncate">{{ $record->name }}</span>

                @if ($record->is_admin)
                    <x-filament::badge color="warning" icon="heroicon-o-shield-check">
                        Admin
                    </x-filament::badge>
                @endif
            </p>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                {{ $record->email }}
            </p>
        </div>
    </div>

    <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
        {{ $record->created_at?->diffForHumans() }}
    </span>
</div>
