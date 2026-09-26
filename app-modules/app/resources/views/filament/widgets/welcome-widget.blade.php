<x-filament-widgets::widget>
    <div class="flex flex-col gap-1 pt-2 sm:pt-4">
        <p class="text-sm font-medium text-gray-500 first-letter:uppercase dark:text-gray-400">
            {{ $this->getToday() }}
        </p>

        <h1 class="text-2xl font-bold tracking-tight text-gray-950 sm:text-3xl dark:text-white">
            {{ $this->getGreeting() }}, {{ $this->getFirstName() }}
        </h1>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            ¿A dónde quieres ir hoy?
        </p>
    </div>
</x-filament-widgets::widget>
