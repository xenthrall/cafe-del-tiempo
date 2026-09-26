@php
    $registrationIsOpen = Route::has('filament.app.auth.register');
    $description = 'Suite personal de código abierto con una bóveda digital zero-knowledge para contraseñas y secretos, y finanzas personales con reportes. Úsala gratis en cafe.tequia.dev o instálala en tu propio servidor.';

    $schema = [
        [
            '@type' => 'SoftwareApplication',
            'name' => config('landing.name'),
            'url' => config('landing.hosted_url'),
            'description' => $description,
            'applicationCategory' => 'SecurityApplication',
            'applicationSubCategory' => 'Gestor de contraseñas y finanzas personales',
            'operatingSystem' => 'Web',
            'inLanguage' => 'es',
            'license' => 'https://opensource.org/licenses/MIT',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
            'featureList' => [
                'Bóveda digital zero-knowledge cifrada en el navegador',
                'Finanzas personales con cuentas, movimientos y reportes en Excel y PDF',
                'Versión alojada gratuita o instalación self-hosted',
            ],
            'author' => ['@type' => 'Organization', 'name' => config('landing.author.name'), 'url' => config('landing.author.url')],
            'sameAs' => [config('landing.repository_url')],
        ],
        [
            '@type' => 'WebSite',
            'name' => config('landing.name'),
            'url' => config('landing.hosted_url'),
            'inLanguage' => 'es',
        ],
    ];
@endphp

<x-landing::layout
    title="Bóveda digital y finanzas personales, privadas y de código abierto"
    :description="$description"
    :schema="$schema"
>
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 md:pt-24 pb-24 flex flex-col gap-28 md:gap-36">
        @include('landing.sections.hero')
        @include('landing.sections.modules')
        @include('landing.sections.privacy')
        @include('landing.sections.philosophy')
        @include('landing.sections.options')
        @include('landing.sections.quick-start')
        @include('landing.sections.closing-cta')
    </main>
</x-landing::layout>
