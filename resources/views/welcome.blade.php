<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        <title>{{ config('app.name', 'Café del Tiempo') }} — Suite Personal Auto-Alojada</title>
        <meta name="description" content="Una suite personal y auto-alojada que crece con el tiempo: hoy incluye una bóveda digital privada y un módulo de finanzas personales, con más herramientas por venir.">

        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('theme');
                    var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#faf8f5] dark:bg-[#0e0c0a] text-[#1c1815] dark:text-[#f3efe8] font-sans antialiased selection:bg-amber-500 selection:text-white transition-colors duration-200">
        <!-- Background Ambient Glow -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[640px] h-[420px] bg-amber-100/40 dark:bg-amber-950/15 blur-3xl rounded-full"></div>
        </div>

        <!-- Navigation Bar -->
        <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-[#faf8f5]/85 dark:bg-[#0e0c0a]/85 border-b border-stone-200/70 dark:border-stone-800/70">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo & Brand -->
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/icon-light.png') }}" alt="" class="w-8 h-8 dark:hidden">
                    <img src="{{ asset('images/icon-dark.png') }}" alt="" class="hidden w-8 h-8 dark:block">
                    <span class="font-semibold text-sm tracking-tight text-stone-900 dark:text-stone-100">
                        {{ config('app.name', 'Café del Tiempo') }}
                    </span>
                </a>

                <!-- Nav Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm text-stone-600 dark:text-stone-400">
                    <a href="#modulos" class="hover:text-stone-900 dark:hover:text-stone-100 transition-colors">Módulos</a>
                    <a href="#filosofia" class="hover:text-stone-900 dark:hover:text-stone-100 transition-colors">Filosofía</a>
                    <a href="#empezar" class="hover:text-stone-900 dark:hover:text-stone-100 transition-colors">Instalación</a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="themeToggle"
                        aria-label="Cambiar entre tema claro y oscuro"
                        class="w-9 h-9 flex items-center justify-center rounded-full text-stone-500 dark:text-stone-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-stone-100 transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                        <svg class="hidden w-[18px] h-[18px] dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 flex flex-col gap-24 md:gap-32">

            <!-- Hero Section -->
            <section class="flex flex-col items-center text-center max-w-2xl mx-auto">
                <span class="text-xs font-medium uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-5">
                    Suite personal auto-alojada
                </span>

                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight text-stone-950 dark:text-stone-50 leading-[1.15] mb-5">
                    Protege hoy lo que trasciende en el tiempo.
                </h1>

                <p class="text-base text-stone-600 dark:text-stone-400 leading-relaxed mb-9">
                    Un espacio digital privado y auto-alojado, organizado en módulos: hoy una bóveda para tus contraseñas y secretos, y finanzas para tus cuentas y movimientos. Con el tiempo, más herramientas se irán sumando.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <a
                            href="{{ route('filament.app.pages.dashboard') }}"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-sm transition-colors"
                        >
                            Ir a mi Bóveda
                        </a>
                    @else
                        <a
                            href="{{ route('filament.app.auth.login') }}"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-sm transition-colors"
                        >
                            Entrar a la Bóveda
                        </a>
                    @endauth

                    <a
                        href="#empezar"
                        class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:border-stone-400 dark:hover:border-stone-600 font-medium text-sm transition-colors"
                    >
                        Instalación rápida
                    </a>
                </div>

                <p class="mt-10 text-xs text-stone-400 dark:text-stone-500">
                    Cifrado AES-256 &middot; 100% self-hosted &middot; Zero-knowledge
                </p>
            </section>

            <!-- Modules Overview -->
            <section id="modulos" class="flex flex-col gap-10">
                <div class="text-center max-w-xl mx-auto">
                    <h2 class="text-2xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight">
                        Una suite, muchos módulos
                    </h2>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mt-2">
                        Cada módulo resuelve una necesidad distinta, todo bajo el mismo techo auto-alojado.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Bóveda module card -->
                    <div class="flex flex-col gap-3 p-6 rounded-2xl border border-stone-200 dark:border-stone-800">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Bóveda Digital</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Contraseñas, secretos, notas confidenciales y cápsulas del tiempo, cifrados antes de guardarse.
                        </p>
                    </div>

                    <!-- Finanzas module card -->
                    <div class="flex flex-col gap-3 p-6 rounded-2xl border border-stone-200 dark:border-stone-800">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Finanzas Personales</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Ingresos, gastos, transferencias y ajustes por cuenta y contexto, con informes en Excel y PDF.
                        </p>
                    </div>
                </div>

                <p class="text-center text-xs text-stone-400 dark:text-stone-500">
                    Más módulos en camino — la suite sigue creciendo con el tiempo.
                </p>
            </section>

            <!-- Philosophy & Manifesto Section -->
            <section id="filosofia" class="max-w-xl mx-auto text-center flex flex-col items-center gap-4 pt-16 border-t border-stone-200/70 dark:border-stone-800/70">
                <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100">
                    La pausa necesaria en un mundo hiperconectado
                </h2>
                <blockquote class="text-sm sm:text-base italic text-stone-600 dark:text-stone-400 leading-relaxed">
                    "Un café del tiempo es la oportunidad de detener el reloj por unos instantes. En un ecosistema digital acelerado y disperso, necesitamos un rincón de calma para organizar lo esencial, cifrar lo vulnerable y asegurar que lo que amamos permanezca a salvo."
                </blockquote>
                <p class="text-xs text-stone-400 dark:text-stone-500">
                    Un proyecto de
                    <a href="https://tequia.dev/" target="_blank" rel="noopener noreferrer" class="font-medium text-amber-700 dark:text-amber-400 hover:underline">Tequia</a>
                </p>
            </section>

            <!-- Quick Start / Getting Started Code Block -->
            <section id="empezar" class="flex flex-col gap-6 max-w-2xl mx-auto w-full">
                <div class="text-center">
                    <h2 class="text-xl sm:text-2xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight">
                        Comienza en tu servidor en minutos
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 mt-1">
                        Desarrollado sobre Laravel y PHP con soporte nativo para SQLite y PostgreSQL.
                    </p>
                </div>

                <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-[#161412] text-stone-200 p-5 shadow-sm font-mono text-xs overflow-x-auto space-y-2">
                    <div class="text-stone-500"># 1. Clona el proyecto y accede al directorio</div>
                    <div class="text-amber-400">git clone https://github.com/xenthrall/cafe-del-tiempo.git</div>
                    <div class="text-amber-400">cd cafe-del-tiempo</div>

                    <div class="text-stone-500 pt-2"># 2. Instala dependencias y prepara el entorno</div>
                    <div class="text-stone-100">composer install && npm install</div>
                    <div class="text-stone-100">cp .env.example .env && php artisan key:generate</div>

                    <div class="text-stone-500 pt-2"># 3. Migra la base de datos y compila activos</div>
                    <div class="text-stone-100">php artisan migrate</div>
                    <div class="text-stone-100">npm run build</div>

                    <div class="text-stone-500 pt-2"># 4. Inicia tu suite personal</div>
                    <div class="text-emerald-400">php artisan serve</div>
                </div>
            </section>

        </main>

        <!-- Footer -->
        <footer class="border-t border-stone-200/70 dark:border-stone-800/70 py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500 dark:text-stone-400">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/icon-light.png') }}" alt="" class="w-4 h-4 dark:hidden">
                    <img src="{{ asset('images/icon-dark.png') }}" alt="" class="hidden w-4 h-4 dark:block">
                    <span>{{ config('app.name', 'Café del Tiempo') }} &bull; Licencia MIT</span>
                </div>

                <div class="flex items-center gap-6">
                    <a href="https://github.com/xenthrall/cafe-del-tiempo" target="_blank" rel="noopener noreferrer" class="hover:text-stone-700 dark:hover:text-stone-200 transition-colors">GitHub</a>
                    <a href="https://tequia.dev/" target="_blank" rel="noopener noreferrer" class="hover:text-stone-700 dark:hover:text-stone-200 transition-colors">Tequia</a>
                </div>
            </div>
        </footer>

        <script>
            (function () {
                var root = document.documentElement;

                document.getElementById('themeToggle').addEventListener('click', function () {
                    var next = root.classList.contains('dark') ? 'light' : 'dark';
                    root.classList.toggle('dark', next === 'dark');

                    try {
                        localStorage.setItem('theme', next);
                    } catch (e) {}
                });

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (event) {
                    try {
                        if (!localStorage.getItem('theme')) {
                            root.classList.toggle('dark', event.matches);
                        }
                    } catch (e) {}
                });
            })();
        </script>
    </body>
</html>
