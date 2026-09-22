<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        <title>@yield('title') — Café del Tiempo</title>

        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('theme');
                    if (stored === 'light' || stored === 'dark') {
                        document.documentElement.setAttribute('data-theme', stored);
                    }
                } catch (e) {}
            })();
        </script>

        <style>
            :root {
                --bg-base: #faf8f5;
                --blob-1: #ffe9d6;
                --blob-2: #e6ecfb;
                --card-bg: #ffffff;
                --card-border: rgba(28, 43, 58, .08);
                --ink: #1c2b3a;
                --muted: #7c8a97;
                --accent: #e8791f;
                --accent-contrast: #ffffff;
                --shadow: rgba(28, 43, 58, .14);
            }

            @media (prefers-color-scheme: dark) {
                :root:not([data-theme="light"]) {
                    --bg-base: #0e161f;
                    --blob-1: rgba(232, 121, 31, .16);
                    --blob-2: rgba(90, 120, 200, .16);
                    --card-bg: #16212c;
                    --card-border: rgba(255, 255, 255, .08);
                    --ink: #f3efe8;
                    --muted: #8b98a6;
                    --accent: #f0913e;
                    --accent-contrast: #16212c;
                    --shadow: rgba(0, 0, 0, .45);
                }
            }

            :root[data-theme="dark"] {
                --bg-base: #0e161f;
                --blob-1: rgba(232, 121, 31, .16);
                --blob-2: rgba(90, 120, 200, .16);
                --card-bg: #16212c;
                --card-border: rgba(255, 255, 255, .08);
                --ink: #f3efe8;
                --muted: #8b98a6;
                --accent: #f0913e;
                --accent-contrast: #16212c;
                --shadow: rgba(0, 0, 0, .45);
            }

            * {
                box-sizing: border-box;
            }

            html, body {
                margin: 0;
                height: 100%;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 1.5rem;
                background:
                    radial-gradient(circle at 12% 12%, var(--blob-1) 0%, transparent 45%),
                    radial-gradient(circle at 90% 88%, var(--blob-2) 0%, transparent 50%),
                    var(--bg-base);
                color: var(--ink);
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                text-align: center;
                transition: background-color .2s ease, color .2s ease;
            }

            .card {
                position: relative;
                width: 100%;
                max-width: 25rem;
                background: var(--card-bg);
                border: 1px solid var(--card-border);
                border-radius: 1.5rem;
                padding: 2.75rem 2.25rem 2.25rem;
                box-shadow: 0 1px 2px var(--shadow), 0 24px 48px -20px var(--shadow);
                animation: rise .5s cubic-bezier(.16, 1, .3, 1);
                transition: background-color .2s ease, border-color .2s ease;
            }

            .theme-toggle {
                position: absolute;
                top: 1rem;
                right: 1rem;
                width: 2.25rem;
                height: 2.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--card-border);
                border-radius: 999px;
                background: transparent;
                color: var(--muted);
                cursor: pointer;
                padding: 0;
                transition: background-color .15s ease, color .15s ease, border-color .15s ease;
            }

            .theme-toggle:hover {
                background: var(--card-border);
                color: var(--ink);
            }

            .theme-toggle:focus-visible {
                outline: 2px solid var(--accent);
                outline-offset: 2px;
            }

            .theme-toggle svg {
                width: 1.1rem;
                height: 1.1rem;
            }

            .theme-toggle .icon-sun {
                display: none;
            }

            :root[data-theme="dark"] .theme-toggle .icon-sun {
                display: block;
            }

            :root[data-theme="dark"] .theme-toggle .icon-moon {
                display: none;
            }

            @media (prefers-color-scheme: dark) {
                :root:not([data-theme="light"]) .theme-toggle .icon-sun {
                    display: block;
                }

                :root:not([data-theme="light"]) .theme-toggle .icon-moon {
                    display: none;
                }
            }

            .icon {
                height: 4.5rem;
                width: 4.5rem;
                margin: 0 auto 1.5rem;
                display: block;
            }

            .icon-dark {
                display: none;
            }

            :root[data-theme="dark"] .icon-light {
                display: none;
            }

            :root[data-theme="dark"] .icon-dark {
                display: block;
            }

            @media (prefers-color-scheme: dark) {
                :root:not([data-theme="light"]) .icon-light {
                    display: none;
                }

                :root:not([data-theme="light"]) .icon-dark {
                    display: block;
                }
            }

            .code {
                margin: 0 0 .5rem;
                font-size: clamp(2.75rem, 9vw, 3.75rem);
                font-weight: 800;
                letter-spacing: -.03em;
                color: var(--accent);
            }

            .message {
                font-size: 1.15rem;
                font-weight: 700;
                margin: 0 0 .5rem;
                letter-spacing: -.01em;
            }

            .hint {
                font-size: .9rem;
                color: var(--muted);
                margin: 0 0 2rem;
                line-height: 1.55;
            }

            .btn {
                display: inline-block;
                padding: .65rem 1.75rem;
                border-radius: 999px;
                background: var(--accent);
                color: var(--accent-contrast);
                text-decoration: none;
                font-weight: 600;
                font-size: .875rem;
                box-shadow: 0 10px 24px -10px var(--accent);
                transition: transform .15s ease, box-shadow .15s ease;
            }

            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 26px -10px var(--accent);
            }

            .btn:focus-visible {
                outline: 2px solid var(--accent);
                outline-offset: 3px;
            }

            @keyframes rise {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @media (prefers-reduced-motion: reduce) {
                .card { animation: none; }
            }
        </style>
    </head>
    <body>
        <div class="card">
            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Cambiar entre tema claro y oscuro">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            <img class="icon icon-light" src="{{ asset('images/icon-light.png') }}" alt="Café del Tiempo">
            <img class="icon icon-dark" src="{{ asset('images/icon-dark.png') }}" alt="Café del Tiempo">

            <div class="code">@yield('code')</div>
            <p class="message">@yield('message')</p>
            <p class="hint">@yield('hint')</p>

            <a href="{{ url('/') }}" class="btn">Volver al inicio</a>
        </div>

        <script>
            (function () {
                var KEY = 'theme';
                var root = document.documentElement;

                document.getElementById('themeToggle').addEventListener('click', function () {
                    var systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var current = root.getAttribute('data-theme');
                    var effectiveDark = current ? current === 'dark' : systemDark;
                    var next = effectiveDark ? 'light' : 'dark';

                    root.setAttribute('data-theme', next);

                    try {
                        localStorage.setItem(KEY, next);
                    } catch (e) {}
                });
            })();
        </script>
    </body>
</html>
