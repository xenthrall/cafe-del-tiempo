<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($this->getApps() as $app)
            @php
                $accent = match ($app['accent']) {
                    'amber' => [
                        'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                        'shortcutIcon' => 'text-amber-600 dark:text-amber-400',
                        'glow' => 'from-amber-50 dark:from-amber-500/5',
                    ],
                    'emerald' => [
                        'icon' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'shortcutIcon' => 'text-emerald-600 dark:text-emerald-400',
                        'glow' => 'from-emerald-50 dark:from-emerald-500/5',
                    ],
                    default => [
                        'icon' => 'bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400',
                        'shortcutIcon' => 'text-primary-600 dark:text-primary-400',
                        'glow' => 'from-primary-50 dark:from-primary-500/5',
                    ],
                };
            @endphp

            <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                {{-- Module entry point --}}
                <a
                    href="{{ $app['url'] }}"
                    class="group relative flex flex-1 items-center gap-4 bg-linear-to-br {{ $accent['glow'] }} to-transparent p-4 transition hover:bg-gray-50 sm:p-5 dark:hover:bg-white/5"
                >
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105 {{ $accent['icon'] }}">
                        <x-filament::icon :icon="$app['icon']" class="size-6" />
                    </span>

                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <span class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $app['label'] }}
                        </span>
                        <span class="text-sm leading-snug text-gray-500 dark:text-gray-400">
                            {{ $app['description'] }}
                        </span>
                    </span>

                    <x-filament::icon
                        icon="heroicon-m-chevron-right"
                        class="size-5 shrink-0 text-gray-300 transition-transform group-hover:translate-x-0.5 group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-400"
                    />
                </a>

                {{-- Section shortcuts: one tap to where the user wants to go --}}
                @if (filled($app['shortcuts']))
                    <nav
                        aria-label="Accesos rápidos de {{ $app['label'] }}"
                        class="grid grid-cols-2 gap-px border-t border-gray-100 bg-gray-100 dark:border-white/5 dark:bg-white/5"
                    >
                        @foreach ($app['shortcuts'] as $shortcut)
                            <a
                                href="{{ $shortcut['url'] }}"
                                class="flex min-h-12 items-center gap-2.5 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition odd:last:col-span-2 hover:bg-gray-50 hover:text-gray-950 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
                            >
                                <x-filament::icon :icon="$shortcut['icon']" class="size-5 shrink-0 {{ $accent['shortcutIcon'] }}" />
                                <span class="truncate">{{ $shortcut['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                @endif
            </article>
        @endforeach
    </div>
</x-filament-widgets::widget>
