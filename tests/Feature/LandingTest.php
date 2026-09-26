<?php

afterEach(function () {
    unset($_SERVER['APP_INSTANCE'], $_ENV['APP_INSTANCE']);
});

test('landing pages point their canonical url to the hosted instance', function (string $path) {
    $this->get($path)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://cafe.tequia.dev'.$path.'">', escape: false);
})->with(['/', '/docs/instalacion']);

test('the landing served from the official domain can be indexed', function () {
    $this->get('https://cafe.tequia.dev/')
        ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', escape: false);
});

test('the landing served from any other domain is not indexed, even in hosted mode', function (string $mode) {
    bootInstance($mode);

    $this->get('https://cafe.example.com/')
        ->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false)
        ->assertSee('<link rel="canonical" href="https://cafe.tequia.dev/">', escape: false);
})->with(['self-hosted', 'hosted']);

test('the sitemap lists the home and every documentation page', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSeeInOrder([
            '<loc>https://cafe.tequia.dev/</loc>',
            '<loc>https://cafe.tequia.dev/docs/instalacion</loc>',
        ], escape: false);
});
