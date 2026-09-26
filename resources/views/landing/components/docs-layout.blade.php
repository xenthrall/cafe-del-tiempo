@props([
    'title',
    'description',
    'tableOfContents' => [],
])

@php
    $baseUrl = config('landing.hosted_url');

    $schema = [
        [
            '@type' => 'TechArticle',
            'headline' => $title,
            'description' => $description,
            'inLanguage' => 'es',
            'url' => $baseUrl.request()->getPathInfo(),
            'author' => ['@type' => 'Organization', 'name' => config('landing.author.name'), 'url' => config('landing.author.url')],
            'isPartOf' => ['@type' => 'WebSite', 'name' => config('landing.name'), 'url' => $baseUrl],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => config('landing.name'), 'item' => $baseUrl.'/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $baseUrl.request()->getPathInfo()],
            ],
        ],
    ];
@endphp

<x-landing::layout :title="$title" :description="$description" type="article" :schema="$schema">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 grid gap-10 lg:grid-cols-[13rem_minmax(0,1fr)] xl:grid-cols-[13rem_minmax(0,1fr)_12rem]">
        <!-- Docs navigation -->
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <nav aria-label="Documentación" class="flex flex-col gap-6 text-sm">
                @foreach (config('landing.docs') as $docsSection)
                    <div class="flex flex-col gap-2">
                        <p class="text-xs font-medium uppercase tracking-wider text-stone-400 dark:text-stone-500">{{ $docsSection['title'] }}</p>
                        @foreach ($docsSection['pages'] as $docsPage)
                            <a
                                href="{{ route($docsPage['route']) }}"
                                @class([
                                    'px-3 py-1.5 -mx-3 rounded-lg transition-colors',
                                    'bg-amber-100/70 text-amber-900 font-medium dark:bg-amber-950/40 dark:text-amber-300' => request()->routeIs($docsPage['route']),
                                    'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100' => ! request()->routeIs($docsPage['route']),
                                ])
                                @if (request()->routeIs($docsPage['route'])) aria-current="page" @endif
                            >{{ $docsPage['title'] }}</a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        <!-- Docs content -->
        <main class="min-w-0">
            <nav aria-label="Ruta" class="mb-6 text-xs text-stone-500 dark:text-stone-400">
                <a href="{{ route('home') }}" class="hover:text-stone-900 dark:hover:text-stone-100">Inicio</a>
                <span class="mx-1.5">/</span>
                <span>Documentación</span>
                <span class="mx-1.5">/</span>
                <span class="text-stone-900 dark:text-stone-100">{{ $title }}</span>
            </nav>

            <article class="flex flex-col gap-14">
                {{ $slot }}
            </article>
        </main>

        <!-- On this page -->
        @if ($tableOfContents)
            <aside class="hidden xl:block xl:sticky xl:top-24 xl:self-start">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-400 dark:text-stone-500 mb-3">En esta página</p>
                <nav aria-label="En esta página" class="flex flex-col gap-2 text-xs">
                    @foreach ($tableOfContents as $anchor => $label)
                        <a href="#{{ $anchor }}" class="text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100">{{ $label }}</a>
                    @endforeach
                </nav>
            </aside>
        @endif
    </div>
</x-landing::layout>
