<section class="grid lg:grid-cols-[1.1fr_1fr] gap-14 lg:gap-12 items-center">
    <div class="flex flex-col items-center lg:items-start text-center lg:text-left">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-amber-200 dark:border-amber-900/60 bg-amber-50/80 dark:bg-amber-950/30 text-xs font-medium text-amber-800 dark:text-amber-300 mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Suite personal de código abierto
        </span>

        <h1 class="text-4xl sm:text-5xl lg:text-[3.4rem] font-semibold tracking-tight text-stone-950 dark:text-stone-50 leading-[1.1] mb-6">
            Protege hoy lo que
            <span class="text-amber-700 dark:text-amber-400">trasciende en el tiempo.</span>
        </h1>

        <p class="text-base sm:text-lg text-stone-600 dark:text-stone-400 leading-relaxed mb-9 max-w-xl">
            Tu espacio digital privado, organizado en módulos: una bóveda para tus contraseñas y secretos, y finanzas para tus cuentas y movimientos. Úsalo gratis en nuestra versión alojada o instálalo en tu propio servidor.
        </p>

        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
            @auth
                <x-landing::button :href="route('filament.app.pages.dashboard')">Ir a mi Bóveda</x-landing::button>
            @else
                @if ($registrationIsOpen)
                    <x-landing::button :href="route('filament.app.auth.register')">
                        Crear cuenta gratis
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </x-landing::button>
                @endif

                <x-landing::button :href="route('filament.app.auth.login')" :variant="$registrationIsOpen ? 'outline' : 'dark'">
                    Entrar a la Bóveda
                </x-landing::button>
            @endauth

            @unless ($registrationIsOpen)
                <x-landing::button href="#opciones" variant="outline">Ver opciones</x-landing::button>
            @endunless
        </div>

        <ul class="mt-10 flex flex-wrap justify-center lg:justify-start gap-x-6 gap-y-2 text-xs text-stone-500 dark:text-stone-400">
            @foreach (['Cifrado AES-256', 'Zero-knowledge en la Bóveda', 'Código abierto (MIT)'] as $highlight)
                <li class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                    {{ $highlight }}
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Hero Preview (decorative) -->
    <div class="relative mx-auto w-full max-w-md" aria-hidden="true">
        <div class="absolute -inset-6 bg-linear-to-tr from-amber-200/40 via-transparent to-emerald-200/30 dark:from-amber-900/20 dark:to-emerald-900/10 blur-2xl rounded-[3rem]"></div>

        <div class="relative rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/90 dark:bg-stone-900/90 shadow-xl shadow-stone-900/5 dark:shadow-black/30 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-stone-100 dark:border-stone-800">
                <div class="flex gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-stone-200 dark:bg-stone-700"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-stone-200 dark:bg-stone-700"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-stone-200 dark:bg-stone-700"></span>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-400">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                    Cifrado en tu navegador
                </span>
            </div>

            <div class="p-4 flex flex-col gap-2.5">
                <p class="text-xs font-semibold text-stone-900 dark:text-stone-100 px-1">Bóveda</p>

                @foreach ([['Correo personal', 'Contraseña', 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400'], ['Banco', 'PIN y clave', 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400'], ['Carta para 2035', 'Cápsula del tiempo', 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400']] as [$itemName, $itemType, $itemColor])
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50/80 dark:bg-stone-950/40">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold {{ $itemColor }}">{{ mb_substr($itemName, 0, 1) }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-stone-800 dark:text-stone-200">{{ $itemName }}</p>
                            <p class="text-[11px] text-stone-400 dark:text-stone-500">{{ $itemType }}</p>
                        </div>
                        <span class="font-mono text-xs tracking-widest text-stone-300 dark:text-stone-600">••••••••</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="relative -mt-10 ml-auto -mr-2 sm:-mr-8 w-56 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-xl shadow-stone-900/10 dark:shadow-black/40 p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-stone-900 dark:text-stone-100">Finanzas</p>
                <span class="text-[11px] text-stone-400 dark:text-stone-500">Últimos 6 meses</span>
            </div>
            <div class="flex items-end gap-1.5 h-16">
                @foreach ([40, 65, 50, 80, 60, 92] as $barHeight)
                    <span class="flex-1 rounded-sm {{ $loop->last ? 'bg-emerald-500' : 'bg-emerald-200 dark:bg-emerald-900/70' }}" style="height: {{ $barHeight }}%"></span>
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-stone-500 dark:text-stone-400">Ahorro del mes <span class="font-semibold text-emerald-600 dark:text-emerald-400">+18%</span></p>
        </div>
    </div>
</section>
