<section id="privacidad" class="flex flex-col gap-12 scroll-mt-24">
    <x-landing::section-heading eyebrow="Privacidad" title="Sin letra pequeña sobre tus datos">
        Cada módulo protege tus datos de la forma que su propósito permite. Así es como funciona cada uno.
    </x-landing::section-heading>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach ([
            [
                'title' => 'Bóveda Digital',
                'accent' => 'amber',
                'browser' => 'Cifra con una clave derivada de tu contraseña',
                'server' => 'Guarda solo datos cifrados que no puede leer',
                'result' => 'Nadie más puede leer tu contenido, ni siquiera nosotros.',
            ],
            [
                'title' => 'Finanzas Personales',
                'accent' => 'emerald',
                'browser' => 'Envía tus movimientos por una conexión segura',
                'server' => 'Los cifra en reposo y calcula tus dashboards',
                'result' => 'Acceso restringido a tu cuenta; el servidor sí puede procesar estos datos.',
            ],
        ] as $privacyFlow)
            @php
                $accentText = $privacyFlow['accent'] === 'amber' ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400';
                $accentBg = $privacyFlow['accent'] === 'amber' ? 'bg-amber-100 dark:bg-amber-950/60' : 'bg-emerald-100 dark:bg-emerald-950/60';
            @endphp
            <div class="flex flex-col gap-5 p-7 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/40">
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">{{ $privacyFlow['title'] }}</h3>

                <div class="grid grid-cols-[1fr_auto_1fr] items-stretch gap-3">
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-950/50 border border-stone-100 dark:border-stone-800">
                        <span class="text-[11px] font-medium uppercase tracking-wider {{ $accentText }}">Tu navegador</span>
                        <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">{{ $privacyFlow['browser'] }}</p>
                    </div>
                    <div class="flex items-center">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center {{ $accentBg }} {{ $accentText }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-950/50 border border-stone-100 dark:border-stone-800">
                        <span class="text-[11px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">Servidor</span>
                        <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">{{ $privacyFlow['server'] }}</p>
                    </div>
                </div>

                <p class="text-sm text-stone-700 dark:text-stone-300">{{ $privacyFlow['result'] }}</p>
            </div>
        @endforeach
    </div>
</section>
