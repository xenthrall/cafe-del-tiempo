<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-squares-2x2">
        <x-slot name="heading">
            Tus módulos
        </x-slot>

        <x-slot name="description">
            Accede rápido a cada herramienta de tu suite.
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getApps() as $app)
                @php
                    $accent = match ($app['accent']) {
                        'amber' => [
                            'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400',
                            'border' => 'hover:border-amber-300 dark:hover:border-amber-700',
                            'arrow' => 'text-amber-700 dark:text-amber-400',
                            'glow' => 'from-amber-100/60 dark:from-amber-500/10',
                        ],
                        'emerald' => [
                            'icon' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400',
                            'border' => 'hover:border-emerald-300 dark:hover:border-emerald-700',
                            'arrow' => 'text-emerald-700 dark:text-emerald-400',
                            'glow' => 'from-emerald-100/60 dark:from-emerald-500/10',
                        ],
                        default => [
                            'icon' => 'bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400',
                            'border' => 'hover:border-primary-300 dark:hover:border-primary-700',
                            'arrow' => 'text-primary-700 dark:text-primary-400',
                            'glow' => 'from-primary-100/60 dark:from-primary-500/10',
                        ],
                    };
                @endphp

                <a
                    href="{{ $app['url'] }}"
                    class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900 {{ $accent['border'] }}"
                >
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br {{ $accent['glow'] }} to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>

                    <div class="relative flex items-start justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl transition-transform group-hover:scale-105 {{ $accent['icon'] }}">
                            <x-filament::icon :icon="$app['icon']" class="h-5 w-5" />
                        </span>

                        <x-filament::icon
                            icon="heroicon-o-arrow-up-right"
                            class="h-4 w-4 text-gray-300 transition-all group-hover:-translate-y-0.5 group-hover:translate-x-0.5 dark:text-gray-600 {{ $accent['arrow'] }} group-hover:opacity-100"
                        />
                    </div>

                    <div class="relative flex flex-col gap-1">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $app['label'] }}
                        </span>
                        <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            {{ $app['description'] }}
                        </p>
                    </div>
                </a>
            @endforeach

            {{-- Recordatorio de que la suite sigue creciendo --}}
            <div class="flex flex-col items-start gap-3 rounded-2xl border border-dashed border-gray-300 p-5 dark:border-white/10">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                    <x-filament::icon icon="heroicon-o-plus" class="h-5 w-5" />
                </span>

                <div class="flex flex-col gap-1">
                    <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                        Más módulos en camino
                    </span>
                    <p class="text-xs leading-relaxed text-gray-400 dark:text-gray-500">
                        Tu suite sigue creciendo — nuevas herramientas se irán sumando aquí con el tiempo.
                    </p>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
