<section id="modulos" class="flex flex-col gap-12 scroll-mt-24">
    <x-landing::section-heading eyebrow="Módulos" title="Una suite, muchos módulos">
        Cada módulo resuelve una necesidad distinta, todo bajo el mismo techo.
    </x-landing::section-heading>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Bóveda module card -->
        <article class="group flex flex-col gap-4 p-7 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/40 hover:border-amber-300 dark:hover:border-amber-800/70 transition-colors">
            <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Bóveda Digital</h3>
                <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    Contraseñas, secretos, notas confidenciales y cápsulas del tiempo, cifrados antes de guardarse.
                </p>
            </div>
            <p class="mt-auto pt-4 border-t border-stone-200 dark:border-stone-800 text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                <span class="font-medium text-amber-700 dark:text-amber-400">Zero-knowledge.</span>
                Cifrado en tu navegador antes de guardarse — ni siquiera nosotros podemos leer tus contraseñas o secretos.
            </p>
        </article>

        <!-- Finanzas module card -->
        <article class="group flex flex-col gap-4 p-7 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/40 hover:border-emerald-300 dark:hover:border-emerald-800/70 transition-colors">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Finanzas Personales</h3>
                <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    Ingresos, gastos, transferencias y ajustes por cuenta y contexto, con informes en Excel y PDF.
                </p>
            </div>
            <p class="mt-auto pt-4 border-t border-stone-200 dark:border-stone-800 text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                <span class="font-medium text-emerald-700 dark:text-emerald-400">Cifrado en reposo y acceso restringido.</span>
                Necesario para ofrecerte dashboards y reportes útiles sobre tus finanzas.
            </p>
        </article>

        <!-- Upcoming modules card -->
        <article class="flex flex-col gap-4 p-7 rounded-2xl border border-dashed border-stone-300 dark:border-stone-700">
            <div class="w-11 h-11 rounded-xl bg-stone-100 dark:bg-stone-800/60 text-stone-500 dark:text-stone-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Más en camino</h3>
                <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    La suite sigue creciendo con el tiempo. Nuevos módulos se suman sin que tengas que migrar a otra herramienta.
                </p>
            </div>
            <a href="{{ config('landing.repository_url') }}" target="_blank" rel="noopener noreferrer" class="mt-auto pt-4 border-t border-dashed border-stone-300 dark:border-stone-700 text-xs font-medium text-stone-600 dark:text-stone-300 hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                Sigue el proyecto en GitHub →
            </a>
        </article>
    </div>
</section>
