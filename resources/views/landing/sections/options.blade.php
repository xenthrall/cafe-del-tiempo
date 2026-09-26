@php
    $hostingOptions = [
        [
            'eyebrow' => 'Versión alojada',
            'title' => 'Crea tu cuenta y listo',
            'featured' => true,
            'rows' => [
                'Para quién' => 'Quien quiere empezar ya, sin servidores ni configuraciones.',
                'Setup' => 'Te registras y usas la bóveda y las finanzas en minutos.',
                'Dónde viven tus datos' => 'En nuestro servidor. La Bóveda llega ya cifrada desde tu navegador; Finanzas se guarda cifrada en reposo y con acceso restringido.',
                'Mantenimiento' => 'Nos encargamos del servidor y las actualizaciones.',
                'Costo' => 'Gratis.',
            ],
        ],
        [
            'eyebrow' => 'Self-hosted',
            'title' => 'Tu propia instancia',
            'featured' => false,
            'rows' => [
                'Para quién' => 'Quien quiere control total, incluso sobre el servidor.',
                'Setup' => 'Clonas el repo e instalas en tu servidor, con Docker o de forma manual.',
                'Dónde viven tus datos' => 'En tu propio servidor, bajo tu control.',
                'Mantenimiento' => 'Corre por tu cuenta: actualizaciones, respaldos y seguridad del servidor.',
                'Costo' => 'Gratis y de código abierto (MIT); solo pagas tu hosting.',
            ],
        ],
    ];
@endphp

<section id="opciones" class="flex flex-col gap-12 scroll-mt-24">
    <x-landing::section-heading eyebrow="Opciones" title="Dos formas de usarla">
        ¿Prefieres empezar sin complicaciones? Crea tu cuenta gratis. ¿Prefieres control total, incluso sobre el servidor? Aloja tu propia instancia. Mismo código, mismo cifrado.
    </x-landing::section-heading>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto w-full">
        @foreach ($hostingOptions as $hostingOption)
            <div @class([
                'relative flex flex-col gap-6 p-7 rounded-2xl',
                'border-2 border-amber-400/70 dark:border-amber-700/70 bg-white dark:bg-stone-900/60 shadow-lg shadow-amber-900/5' => $hostingOption['featured'],
                'border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/40' => ! $hostingOption['featured'],
            ])>
                @if ($hostingOption['featured'])
                    <span class="absolute -top-3 left-7 px-2.5 py-0.5 rounded-full bg-amber-600 text-white text-[11px] font-medium">Recomendado para empezar</span>
                @endif

                <div>
                    <span @class([
                        'text-xs font-medium uppercase tracking-wider',
                        'text-amber-700 dark:text-amber-400' => $hostingOption['featured'],
                        'text-stone-500 dark:text-stone-400' => ! $hostingOption['featured'],
                    ])>{{ $hostingOption['eyebrow'] }}</span>
                    <h3 class="text-xl font-semibold text-stone-900 dark:text-stone-100 mt-1">{{ $hostingOption['title'] }}</h3>
                </div>

                <dl class="flex flex-col divide-y divide-stone-100 dark:divide-stone-800 text-sm">
                    @foreach ($hostingOption['rows'] as $label => $value)
                        <div class="grid grid-cols-[8.5rem_1fr] gap-3 py-2.5">
                            <dt class="text-xs text-stone-400 dark:text-stone-500 pt-0.5">{{ $label }}</dt>
                            <dd class="text-stone-700 dark:text-stone-300">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-auto">
                    @if (! $hostingOption['featured'])
                        <x-landing::button :href="route('docs.installation')" variant="outline" class="w-full">
                            Ver guía de instalación
                        </x-landing::button>
                    @elseif (auth()->check())
                        <x-landing::button :href="route('filament.app.pages.dashboard')" class="w-full">Ir al panel</x-landing::button>
                    @elseif ($registrationIsOpen)
                        <x-landing::button :href="route('filament.app.auth.register')" class="w-full">Crear cuenta gratis</x-landing::button>
                    @else
                        <x-landing::button :href="config('landing.hosted_url').'/app/register'" target="_blank" rel="noopener noreferrer" class="w-full">
                            Crear cuenta en {{ parse_url(config('landing.hosted_url'), PHP_URL_HOST) }}
                        </x-landing::button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
