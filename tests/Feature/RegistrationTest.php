<?php

/**
 * Panel routes are registered while the app boots, so the instance mode has
 * to be set in the environment before booting a fresh application.
 */
function bootInstance(string $mode): void
{
    $_SERVER['APP_INSTANCE'] = $_ENV['APP_INSTANCE'] = $mode;

    test()->refreshApplication();
}

afterEach(function () {
    unset($_SERVER['APP_INSTANCE'], $_ENV['APP_INSTANCE']);
});

test('self-hosted instances keep registration closed by default', function () {
    $this->get('/app/register')->assertNotFound();

    $this->get('/')
        ->assertOk()
        ->assertSee('https://cafe.tequia.dev/app/register');
});

test('hosted instances open registration and link to it from the landing', function () {
    bootInstance('hosted');

    $this->get('/app/register')->assertOk();

    $this->get('/')
        ->assertOk()
        ->assertSee(route('filament.app.auth.register'))
        ->assertDontSee('https://cafe.tequia.dev/app/register');
});
