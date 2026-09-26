<footer class="border-t border-stone-200/70 dark:border-stone-800/70 mt-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div class="flex flex-col gap-3 lg:col-span-2">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/icon-light.png') }}" alt="" width="20" height="20" class="w-5 h-5 dark:hidden">
                <img src="{{ asset('images/icon-dark.png') }}" alt="" width="20" height="20" class="hidden w-5 h-5 dark:block">
                <span class="font-semibold text-stone-900 dark:text-stone-100">{{ config('landing.name') }}</span>
            </a>
            <p class="max-w-sm text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                Suite personal de código abierto: bóveda digital zero-knowledge y finanzas personales. Úsala en la versión alojada o en tu propio servidor.
            </p>
        </div>

        <nav aria-label="Producto" class="flex flex-col gap-2">
            <p class="text-xs font-medium uppercase tracking-wider text-stone-400 dark:text-stone-500">Producto</p>
            <a href="{{ route('home') }}#modulos" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">Módulos</a>
            <a href="{{ route('home') }}#privacidad" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">Privacidad</a>
            <a href="{{ route('home') }}#opciones" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">Alojada o self-hosted</a>
        </nav>

        <nav aria-label="Recursos" class="flex flex-col gap-2">
            <p class="text-xs font-medium uppercase tracking-wider text-stone-400 dark:text-stone-500">Recursos</p>
            @foreach (config('landing.docs') as $docsSection)
                @foreach ($docsSection['pages'] as $docsPage)
                    <a href="{{ route($docsPage['route']) }}" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">{{ $docsPage['title'] }}</a>
                @endforeach
            @endforeach
            <a href="{{ config('landing.repository_url') }}" target="_blank" rel="noopener noreferrer" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">GitHub</a>
            <a href="{{ config('landing.author.url') }}" target="_blank" rel="noopener noreferrer" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">{{ config('landing.author.name') }}</a>
        </nav>
    </div>

    <div class="border-t border-stone-200/70 dark:border-stone-800/70">
        <p class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-xs text-stone-500 dark:text-stone-400">
            © {{ date('Y') }} {{ config('landing.name') }} &bull; Licencia MIT
        </p>
    </div>
</footer>
