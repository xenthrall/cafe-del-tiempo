@props([
    'title',
    'description',
    'type' => 'website',
    'schema' => [],
])

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        @include('landing.partials.seo', [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'schema' => $schema,
        ])

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

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#faf8f5] dark:bg-[#0e0c0a] text-[#1c1815] dark:text-[#f3efe8] font-sans antialiased selection:bg-amber-500 selection:text-white transition-colors duration-200">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:rounded-lg focus:bg-amber-600 focus:text-white text-sm">
            Saltar al contenido
        </a>

        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[520px] bg-amber-100/50 dark:bg-amber-950/20 blur-3xl rounded-full"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(120,113,108,0.12)_1px,transparent_0)] bg-size-[24px_24px] mask-[linear-gradient(to_bottom,black,transparent_60%)]"></div>
        </div>

        @include('landing.partials.header')

        <div id="contenido">
            {{ $slot }}
        </div>

        @include('landing.partials.footer')

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

                document.querySelectorAll('[data-copy]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var code = button.closest('[data-code-block]').querySelector('pre').innerText.trim();
                        var label = button.textContent;

                        navigator.clipboard.writeText(code).then(function () {
                            button.textContent = '¡Copiado!';
                            setTimeout(function () { button.textContent = label; }, 2000);
                        }).catch(function () {});
                    });
                });
            })();
        </script>
    </body>
</html>
