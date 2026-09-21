<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Café del Tiempo') }} — Suite Personal Auto-Alojada</title>
        <meta name="description" content="Una suite personal y auto-alojada que crece con el tiempo: hoy incluye una bóveda digital privada y un módulo de finanzas personales, con más herramientas por venir.">

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#faf8f5] dark:bg-[#0f0d0b] text-[#241f1c] dark:text-[#ede5dc] font-sans antialiased selection:bg-amber-500 selection:text-white transition-colors duration-200">
        <!-- Background Ambient Glow -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[720px] h-[480px] bg-gradient-to-b from-amber-200/40 via-orange-100/20 to-transparent dark:from-amber-950/25 dark:via-amber-900/10 dark:to-transparent blur-3xl rounded-full"></div>
            <div class="absolute top-1/2 -right-40 w-[480px] h-[480px] bg-amber-100/30 dark:bg-amber-950/15 blur-3xl rounded-full"></div>
        </div>

        <!-- Navigation Bar -->
        <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-[#faf8f5]/85 dark:bg-[#0f0d0b]/85 border-b border-stone-200/80 dark:border-stone-800/80">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo & Brand -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img
                        src="{{ asset('images/icon-light.png') }}"
                        alt=""
                        class="w-10 h-10 dark:hidden group-hover:scale-105 transition-transform duration-200"
                    >
                    <img
                        src="{{ asset('images/icon-dark.png') }}"
                        alt=""
                        class="hidden w-10 h-10 dark:block group-hover:scale-105 transition-transform duration-200"
                    >
                    <div class="flex flex-col">
                        <span class="font-semibold text-base tracking-tight text-stone-900 dark:text-stone-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">
                            {{ config('app.name', 'Café del Tiempo') }}
                        </span>
                        <span class="text-[11px] text-stone-500 dark:text-stone-400 font-normal">
                            Suite Personal
                        </span>
                    </div>
                </a>

                <!-- Nav Links -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-stone-600 dark:text-stone-300">
                    <a href="#modulos" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">Módulos</a>
                    <a href="#boveda" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">Bóveda</a>
                    <a href="#finanzas" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">Finanzas</a>
                    <a href="#filosofia" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">Filosofía</a>
                </nav>

                <!-- Actions / Auth -->
                <div class="flex items-center gap-3">
                    <a
                        href="https://github.com/xenthrall/cafe-del-tiempo"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-stone-700 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white border border-stone-300 dark:border-stone-700 hover:border-stone-400 dark:hover:border-stone-600 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        <span>GitHub</span>
                    </a>

                    @auth
                        <a
                            href="{{ route('filament.app.pages.dashboard') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-xs sm:text-sm shadow-sm transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Ir al panel</span>
                        </a>
                    @else
                        <a
                            href="{{ route('filament.app.auth.login') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs sm:text-sm shadow-sm transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Iniciar sesión</span>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20 flex flex-col gap-20 md:gap-28">

            <!-- Hero Section -->
            <section class="flex flex-col items-center text-center max-w-3xl mx-auto">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-amber-300/60 dark:border-amber-700/50 bg-amber-50/80 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs font-medium mb-6 backdrop-blur-sm shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Suite Personal Auto-Alojada &bull; Módulos en constante crecimiento</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tight text-stone-950 dark:text-stone-50 leading-[1.15] mb-6">
                    Protege hoy lo que <span class="bg-gradient-to-r from-amber-700 via-amber-600 to-yellow-600 dark:from-amber-400 dark:via-amber-300 dark:to-yellow-200 bg-clip-text text-transparent">trasciende en el tiempo</span>.
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-lg text-stone-600 dark:text-stone-300 max-w-2xl leading-relaxed mb-8">
                    Un espacio digital privado, seguro y auto-alojado, organizado en módulos: hoy una bóveda para tus contraseñas y secretos, y un módulo de finanzas para tus cuentas y movimientos. Con el tiempo, más herramientas se irán sumando, creando poco a poco un espacio propio, tranquilo y duradero.
                </p>

                <!-- CTA Group -->
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 w-full sm:w-auto">
                    @auth
                        <a
                            href="{{ route('filament.app.pages.dashboard') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-sm shadow-md shadow-stone-900/10 hover:shadow-lg transition-all transform hover:-translate-y-0.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Ir a mi Bóveda</span>
                        </a>
                    @else
                        <a
                            href="{{ route('filament.app.auth.login') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-white dark:bg-amber-600 dark:hover:bg-amber-500 font-medium text-sm shadow-md shadow-stone-900/10 hover:shadow-lg transition-all transform hover:-translate-y-0.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Entrar a la Bóveda</span>
                        </a>
                    @endauth

                    <a
                        href="#empezar"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white/70 dark:bg-stone-900/70 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-800 dark:text-stone-200 font-medium text-sm transition-all"
                    >
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span>Instalación Rápida</span>
                    </a>
                </div>

                <!-- Trust Micro Badges -->
                <div class="mt-10 pt-6 border-t border-stone-200/70 dark:border-stone-800/70 flex flex-wrap items-center justify-center gap-6 text-xs text-stone-500 dark:text-stone-400">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Cifrado AES-256
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        100% Self-Hosted
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10M4 18h10" />
                        </svg>
                        Múltiples Módulos
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Zero-Knowledge & Privacidad
                    </span>
                </div>
            </section>

            <!-- Modules Overview -->
            <section id="modulos" class="flex flex-col gap-10">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-amber-700 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider">
                        Una suite, muchos módulos
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight mt-1">
                        Café del Tiempo crece contigo
                    </h2>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mt-2">
                        No es una sola herramienta: es una plataforma personal donde cada módulo resuelve una necesidad distinta, todo bajo el mismo techo auto-alojado.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Bóveda module card -->
                    <a href="#boveda" class="group flex flex-col gap-3 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-amber-300 dark:hover:border-amber-700 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Bóveda Digital</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Contraseñas, secretos, notas confidenciales y cápsulas del tiempo, cifrados antes de guardarse.
                        </p>
                        <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                            Ver módulo
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </a>

                    <!-- Finanzas module card -->
                    <a href="#finanzas" class="group flex flex-col gap-3 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Finanzas Personales</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Ingresos, gastos, transferencias y ajustes organizados por cuenta, categoría y contexto, con informes en Excel y PDF.
                        </p>
                        <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                            Ver módulo
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </a>

                    <!-- More modules coming -->
                    <div class="flex flex-col gap-3 p-6 rounded-2xl border border-dashed border-stone-300 dark:border-stone-700 bg-stone-50/40 dark:bg-stone-900/30">
                        <div class="w-10 h-10 rounded-xl bg-stone-200/70 dark:bg-stone-800/70 text-stone-500 dark:text-stone-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-700 dark:text-stone-300">Más módulos en camino</h3>
                        <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 leading-relaxed">
                            Café del Tiempo sigue creciendo — nuevas herramientas se irán sumando a la suite con el tiempo.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Vault Showcase Interactive Mockup -->
            <section id="boveda" class="w-full flex flex-col gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Módulo &bull; Bóveda Digital
                </div>

                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white/90 dark:bg-stone-900/90 shadow-xl overflow-hidden backdrop-blur-sm">
                    <!-- Window Top Bar -->
                    <div class="px-5 py-3.5 bg-stone-100/80 dark:bg-stone-950/80 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400/80"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400/80"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400/80"></span>
                            <span class="ml-2 text-xs font-mono text-stone-500 dark:text-stone-400 hidden sm:inline">
                                boveda.cafe-del-tiempo.local &bull; sesión segura
                            </span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Bóveda Bloqueada • Clave Cifrada</span>
                        </div>
                    </div>

                    <!-- Inner Mockup Body -->
                    <div class="grid grid-cols-1 md:grid-cols-12 divide-y md:divide-y-0 md:divide-x divide-stone-200 dark:divide-stone-800">
                        <!-- Sidebar Mockup -->
                        <div class="md:col-span-4 p-5 bg-stone-50/50 dark:bg-stone-950/40 flex flex-col gap-4 text-xs">
                            <div class="text-[11px] font-semibold text-stone-400 uppercase tracking-wider">
                                Espacios Protegidos
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-amber-100/70 dark:bg-amber-950/50 text-amber-900 dark:text-amber-200 font-medium">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Bóveda Principal
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-200/60 dark:bg-amber-900/60 font-mono">18</span>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/50 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Cápsulas del Tiempo
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono">3</span>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/50 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Notas Confidenciales
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono">7</span>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/50 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Legado & Rescate
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono">1</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-stone-200 dark:border-stone-800 text-[11px] text-stone-500">
                                <p class="font-medium text-stone-700 dark:text-stone-300">Estado de Resguardo</p>
                                <p class="mt-0.5">Base SQLite local &bull; Cifrado activo</p>
                            </div>
                        </div>

                        <!-- Content List Mockup -->
                        <div class="md:col-span-8 p-5 sm:p-6 space-y-3">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h3 class="text-sm font-semibold text-stone-900 dark:text-stone-100">Elementos Protegidos</h3>
                                    <p class="text-xs text-stone-500">Todo el contenido reside cifrado antes de almacenarse.</p>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-mono rounded bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                    AES-256-GCM
                                </span>
                            </div>

                            <!-- Mock Card 1 -->
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60 flex items-center justify-between gap-3 hover:border-amber-400 dark:hover:border-amber-600 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-semibold text-stone-900 dark:text-stone-100">Cápsula: Recuerdos & Metas para 2028</p>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-700 dark:text-amber-300 font-medium">Temporizador</span>
                                        </div>
                                        <p class="text-[11px] text-stone-500">Desbloqueo programado &bull; 1 de enero de 2028 (en 480 días)</p>
                                    </div>
                                </div>
                                <span class="text-xs font-mono text-stone-400">&#128274; Bloqueado</span>
                            </div>

                            <!-- Mock Card 2 -->
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60 flex items-center justify-between gap-3 hover:border-amber-400 dark:hover:border-amber-600 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-semibold text-stone-900 dark:text-stone-100">Claves Maestras de Infraestructura & Servidores</p>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 font-medium">Bóveda</span>
                                        </div>
                                        <p class="text-[11px] text-stone-500">Credenciales SSH, llaves GPG y frases de respaldo &bull; Actualizado hoy</p>
                                    </div>
                                </div>
                                <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400">Protegido</span>
                            </div>

                            <!-- Mock Card 3 -->
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60 flex items-center justify-between gap-3 hover:border-amber-400 dark:hover:border-amber-600 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-semibold text-stone-900 dark:text-stone-100">Protocolo de Legado & Instrucciones Familiares</p>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-orange-500/10 text-orange-700 dark:text-orange-300 font-medium">Emergencia</span>
                                        </div>
                                        <p class="text-[11px] text-stone-500">Activación condicionada a inactividad prolongada &bull; 2 contactos clave</p>
                                    </div>
                                </div>
                                <span class="text-xs font-mono text-stone-400">&#128225; En espera</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Vault Features Grid -->
            <section id="boveda-caracteristicas" class="flex flex-col gap-10">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-amber-700 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider">
                        Bóveda Digital &bull; Todo lo que incluye
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight mt-1">
                        Mucho más que un gestor de secretos: un refugio en el tiempo
                    </h2>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mt-2">
                        Herramientas diseñadas para preservar la información más crítica de tu vida con privacidad inviolable.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Bóveda Cifrada</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Guarda contraseñas, semillas criptográficas, tokens API y notas confidenciales. Cifrado simétrico donde nadie, ni el servidor, puede leer tus datos sin tu clave.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Cápsulas del Tiempo</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Escribe para el futuro. Bloquea mensajes, memorias o documentos confidenciales para que sólo puedan ser desencriptados en una fecha o evento señalado.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Legado y Recuperación</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Define protocolos seguros para compartir información vital con personas designadas ante imprevistos, evitando que tus datos cruciales se pierdan para siempre.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">100% Soberano</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Alójalo en tu propia máquina o servidor casero. Sin suscripciones forzadas, sin telemetría corporativa ni dependencia de nubes comerciales.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Finance Showcase Interactive Mockup -->
            <section id="finanzas" class="w-full flex flex-col gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Módulo &bull; Finanzas Personales
                </div>

                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white/90 dark:bg-stone-900/90 shadow-xl overflow-hidden backdrop-blur-sm">
                    <!-- Window Top Bar -->
                    <div class="px-5 py-3.5 bg-stone-100/80 dark:bg-stone-950/80 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400/80"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400/80"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400/80"></span>
                            <span class="ml-2 text-xs font-mono text-stone-500 dark:text-stone-400 hidden sm:inline">
                                finanzas.cafe-del-tiempo.local &bull; resumen del mes
                            </span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Balance saludable</span>
                        </div>
                    </div>

                    <!-- Inner Mockup Body -->
                    <div class="p-5 sm:p-6 flex flex-col gap-5">
                        <!-- KPI row -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60">
                                <p class="text-[11px] text-stone-500">Saldo total</p>
                                <p class="mt-1 text-lg font-semibold text-stone-900 dark:text-stone-100">$ 4.280.000</p>
                            </div>
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60">
                                <p class="text-[11px] text-stone-500">Ingresos</p>
                                <p class="mt-1 text-lg font-semibold text-emerald-600 dark:text-emerald-400">$ 3.100.000</p>
                            </div>
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60">
                                <p class="text-[11px] text-stone-500">Gastos</p>
                                <p class="mt-1 text-lg font-semibold text-rose-600 dark:text-rose-400">$ 1.860.000</p>
                            </div>
                            <div class="p-3.5 rounded-xl border border-stone-200/80 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60">
                                <p class="text-[11px] text-stone-500">Neto del mes</p>
                                <p class="mt-1 text-lg font-semibold text-stone-900 dark:text-stone-100">$ 1.240.000</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Recent movements mock -->
                            <div class="flex flex-col gap-2">
                                <h3 class="text-sm font-semibold text-stone-900 dark:text-stone-100">Movimientos recientes</h3>
                                <div class="flex flex-col divide-y divide-stone-200 dark:divide-stone-800 rounded-xl border border-stone-200/80 dark:border-stone-800 overflow-hidden">
                                    <div class="flex items-center justify-between px-3.5 py-2.5 bg-stone-50/70 dark:bg-stone-950/60">
                                        <span class="text-xs text-stone-600 dark:text-stone-400">Salario &bull; Personal</span>
                                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">+ $ 2.500.000</span>
                                    </div>
                                    <div class="flex items-center justify-between px-3.5 py-2.5 bg-stone-50/70 dark:bg-stone-950/60">
                                        <span class="text-xs text-stone-600 dark:text-stone-400">Combustible &bull; Vehículo</span>
                                        <span class="text-xs font-medium text-rose-600 dark:text-rose-400">- $ 180.000</span>
                                    </div>
                                    <div class="flex items-center justify-between px-3.5 py-2.5 bg-stone-50/70 dark:bg-stone-950/60">
                                        <span class="text-xs text-stone-600 dark:text-stone-400">Mercado &bull; Personal</span>
                                        <span class="text-xs font-medium text-rose-600 dark:text-rose-400">- $ 320.000</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Expense by context mock -->
                            <div class="flex flex-col gap-2">
                                <h3 class="text-sm font-semibold text-stone-900 dark:text-stone-100">Gastos por contexto</h3>
                                <div class="flex flex-col gap-3 rounded-xl border border-stone-200/80 dark:border-stone-800 p-3.5 bg-stone-50/70 dark:bg-stone-950/60">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-600 dark:text-stone-400">Personal</span>
                                            <span class="font-medium text-stone-900 dark:text-stone-100">$ 1.240.000</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-stone-200 dark:bg-white/10">
                                            <div class="h-full w-[80%] rounded-full bg-rose-500/80"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-600 dark:text-stone-400">Vehículo</span>
                                            <span class="font-medium text-stone-900 dark:text-stone-100">$ 480.000</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-stone-200 dark:bg-white/10">
                                            <div class="h-full w-[35%] rounded-full bg-rose-500/80"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-600 dark:text-stone-400">Trabajo</span>
                                            <span class="font-medium text-stone-900 dark:text-stone-100">$ 140.000</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-stone-200 dark:bg-white/10">
                                            <div class="h-full w-[15%] rounded-full bg-rose-500/80"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Finance Features Grid -->
            <section id="finanzas-caracteristicas" class="flex flex-col gap-10">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-emerald-700 dark:text-emerald-400 text-xs font-semibold uppercase tracking-wider">
                        Finanzas Personales &bull; Todo lo que incluye
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight mt-1">
                        Claridad sobre a dónde va tu dinero, sin hojas de cálculo sueltas
                    </h2>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mt-2">
                        Un panel financiero pensado para entenderse de un vistazo, no para atormentarte con contabilidad.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Movimientos claros</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Ingresos, gastos, transferencias entre tus cuentas y ajustes de saldo, cada uno con su propia categoría y contexto.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Cuentas y saldos en vivo</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Saldo consolidado y por cuenta, calculado al instante. Archiva las que ya no uses sin perder su historial.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Contextos financieros</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Separa "Personal", "Vehículo" o cualquier otra actividad para analizar cada una por aparte, sin mezclarlas.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M4 7h16M4 7a2 2 0 012-2h12a2 2 0 012 2M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Informes en Excel y PDF</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                            Descarga justo lo que estás viendo filtrado en pantalla, listo para revisar o compartir cuando lo necesites.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Philosophy & Manifesto Section -->
            <section id="filosofia" class="p-8 sm:p-12 rounded-3xl border border-amber-200/80 dark:border-amber-900/50 bg-gradient-to-br from-amber-50/70 via-stone-50 to-orange-50/40 dark:from-stone-900 dark:via-stone-900/90 dark:to-amber-950/30">
                <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-4">
                    <span class="text-2xl">☕</span>
                    <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight text-stone-900 dark:text-stone-100">
                        La pausa necesaria en un mundo hiperconectado
                    </h2>
                    <blockquote class="text-sm sm:text-base italic text-stone-700 dark:text-stone-300 leading-relaxed">
                        "Un café del tiempo es la oportunidad de detener el reloj por unos instantes. En un ecosistema digital acelerado y disperso, necesitamos un rincón de calma para organizar lo esencial, cifrar lo vulnerable y asegurar que lo que amamos permanezca a salvo."
                    </blockquote>
                    <p class="text-xs text-stone-500 dark:text-stone-400">
                        &mdash; Manifiesto de Café del Tiempo
                    </p>
                    <p class="text-[11px] text-stone-400 dark:text-stone-500">
                        Un proyecto de
                        <a href="https://tequia.dev/" target="_blank" rel="noopener noreferrer" class="font-medium text-amber-700 dark:text-amber-400 hover:underline">Tequia</a>
                    </p>
                </div>
            </section>

            <!-- Quick Start / Getting Started Code Block -->
            <section id="empezar" class="flex flex-col gap-6 max-w-3xl mx-auto w-full">
                <div class="text-center">
                    <span id="seguridad" class="text-amber-700 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider">
                        Puesta en marcha
                    </span>
                    <h2 class="text-2xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight mt-1">
                        Comienza en tu servidor en cuestión de minutos
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 mt-1">
                        Desarrollado sobre Laravel y PHP con soporte nativo para SQLite y PostgreSQL.
                    </p>
                </div>

                <!-- Code Container -->
                <div class="rounded-2xl border border-stone-800 bg-[#161412] text-stone-200 p-5 shadow-lg font-mono text-xs overflow-x-auto space-y-2">
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
        <footer class="mt-20 border-t border-stone-200/80 dark:border-stone-800/80 py-10 bg-white/50 dark:bg-stone-950/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500 dark:text-stone-400">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-light.png') }}" alt="{{ config('app.name', 'Café del Tiempo') }}" class="h-6 dark:hidden">
                        <img src="{{ asset('images/logo-dark.png') }}" alt="{{ config('app.name', 'Café del Tiempo') }}" class="hidden h-6 dark:block">
                        <span>&bull; Licencia MIT</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <a href="https://github.com/xenthrall/cafe-del-tiempo" target="_blank" rel="noopener noreferrer" class="hover:text-amber-600 transition-colors">GitHub</a>
                        <a href="https://laravel.com" target="_blank" rel="noopener noreferrer" class="hover:text-amber-600 transition-colors">Laravel</a>
                        <a href="https://tailwindcss.com" target="_blank" rel="noopener noreferrer" class="hover:text-amber-600 transition-colors">Tailwind CSS</a>
                    </div>
                    <div>
                        Construido con cuidado para proteger lo que perdura.
                    </div>
                </div>

                <div class="pt-6 border-t border-stone-200/70 dark:border-stone-800/70 text-center text-[11px] text-stone-400 dark:text-stone-500">
                    Diseñado y desarrollado por
                    <a href="https://tequia.dev/" target="_blank" rel="noopener noreferrer" class="font-medium text-amber-700 dark:text-amber-400 hover:underline">Tequia</a>
                </div>
            </div>
        </footer>
    </body>
</html>
