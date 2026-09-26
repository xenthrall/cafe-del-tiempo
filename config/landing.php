<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | Public name of the project. It is kept apart from APP_NAME so that SEO
    | metadata stays consistent regardless of how an instance is configured.
    |
    */

    'name' => 'Café del Tiempo',

    /*
    |--------------------------------------------------------------------------
    | Public URLs
    |--------------------------------------------------------------------------
    |
    | "hosted_url" is the official public instance. It is the canonical base
    | for every landing page, and only requests served from its host are
    | indexable, so copies of the landing never compete with it in search
    | results. It is fixed on purpose instead of being read from the env.
    |
    */

    'hosted_url' => 'https://cafe.tequia.dev',

    'repository_url' => 'https://github.com/xenthrall/cafe-del-tiempo',

    'author' => [
        'name' => 'Tequia',
        'url' => 'https://tequia.dev',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation
    |--------------------------------------------------------------------------
    |
    | Sections and pages shown in the docs sidebar and in the sitemap. Add a
    | page here (plus its route and view) to publish it, e.g. architecture.
    |
    */

    'docs' => [
        [
            'title' => 'Primeros pasos',
            'pages' => [
                [
                    'title' => 'Instalación',
                    'route' => 'docs.installation',
                ],
            ],
        ],
    ],

];
