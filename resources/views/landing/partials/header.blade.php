@php
    $navigationLinks = [
        'Módulos' => route('home').'#modulos',
        'Privacidad' => route('home').'#privacidad',
        'Opciones' => route('home').'#opciones',
        'Instalación' => route('docs.installation'),
    ];
@endphp

<header class="sticky top-0 z-40 w-full backdrop-blur-md bg-[#faf8f5]/80 dark:bg-[#0e0c0a]/80 border-b border-stone-200/70 dark:border-stone-800/70">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
            <img src="{{ asset('images/icon-light.png') }}" alt="" width="32" height="32" class="w-8 h-8 dark:hidden">
            <img src="{{ asset('images/icon-dark.png') }}" alt="" width="32" height="32" class="hidden w-8 h-8 dark:block">
            <span class="font-semibold text-sm tracking-tight text-stone-900 dark:text-stone-100">
                {{ config('landing.name') }}
            </span>
        </a>

        <nav aria-label="Principal" class="hidden md:flex items-center gap-8 text-sm text-stone-600 dark:text-stone-400">
            @foreach ($navigationLinks as $label => $href)
                <a
                    href="{{ $href }}"
                    @class([
                        'transition-colors hover:text-stone-900 dark:hover:text-stone-100',
                        'font-medium text-stone-900 dark:text-stone-100' => $href === url()->current(),
                    ])
                >{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-1.5">
            <a
                href="{{ config('landing.repository_url') }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Código fuente en GitHub"
                class="hidden sm:flex w-9 h-9 items-center justify-center rounded-full text-stone-500 dark:text-stone-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-stone-100 transition-colors"
            >
                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 .5a11.5 11.5 0 0 0-3.64 22.41c.58.1.79-.25.79-.56v-2c-3.2.7-3.88-1.37-3.88-1.37-.52-1.33-1.28-1.69-1.28-1.69-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11 11 0 0 1 5.77 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.83 1.19 3.09 0 4.42-2.7 5.4-5.26 5.68.41.36.78 1.06.78 2.14v3.17c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .5z"/>
                </svg>
            </a>

            <button
                type="button"
                id="themeToggle"
                aria-label="Cambiar entre tema claro y oscuro"
                class="w-9 h-9 flex items-center justify-center rounded-full text-stone-500 dark:text-stone-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-stone-100 transition-colors"
            >
                <svg class="w-[18px] h-[18px] dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <svg class="hidden w-[18px] h-[18px] dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                </svg>
            </button>

            @auth
                <a
                    href="{{ route('filament.app.pages.dashboard') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-xs sm:text-sm transition-colors"
                >
                    Ir al panel
                </a>
            @else
                <a
                    href="{{ route('filament.app.auth.login') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-xs sm:text-sm transition-colors"
                >
                    Iniciar sesión
                </a>
            @endauth

            <details class="relative md:hidden group">
                <summary class="list-none w-9 h-9 flex items-center justify-center rounded-full text-stone-500 dark:text-stone-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 cursor-pointer [&::-webkit-details-marker]:hidden" aria-label="Abrir menú">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </summary>
                <nav aria-label="Principal móvil" class="absolute right-0 mt-2 w-52 p-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-lg flex flex-col text-sm">
                    @foreach ($navigationLinks as $label => $href)
                        <a href="{{ $href }}" class="px-3 py-2 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800">{{ $label }}</a>
                    @endforeach
                    <a href="{{ config('landing.repository_url') }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800">GitHub</a>
                </nav>
            </details>
        </div>
    </div>
</header>
