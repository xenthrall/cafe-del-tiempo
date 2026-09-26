<section class="flex flex-col items-center text-center gap-6 max-w-2xl mx-auto">
    <h2 class="text-3xl sm:text-4xl font-semibold tracking-tight text-stone-950 dark:text-stone-50">
        Tómate un café con tu tiempo.
    </h2>
    <p class="text-sm sm:text-base text-stone-600 dark:text-stone-400">
        Ordena lo esencial y cifra lo vulnerable, a tu ritmo.
    </p>
    <div class="flex flex-wrap items-center justify-center gap-3">
        @auth
            <x-landing::button :href="route('filament.app.pages.dashboard')">Ir al panel</x-landing::button>
        @else
            @if ($registrationIsOpen)
                <x-landing::button :href="route('filament.app.auth.register')">Crear cuenta gratis</x-landing::button>
            @endif

            <x-landing::button :href="route('filament.app.auth.login')" :variant="$registrationIsOpen ? 'outline' : 'dark'">
                Iniciar sesión
            </x-landing::button>
        @endauth
    </div>
</section>
