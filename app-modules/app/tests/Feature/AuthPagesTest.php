<?php

use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tequia\App\Filament\Auth\Login;

beforeEach(function () {
    Filament::setCurrentPanel('app');
});

afterEach(function () {
    unset($_SERVER['APP_INSTANCE'], $_ENV['APP_INSTANCE']);
});

test('the login page renders inside the branded auth layout', function () {
    $this->get('/app/login')
        ->assertOk()
        ->assertSee('Tu rincón de calma te estaba esperando.')
        ->assertSee('Volver al inicio');
});

test('the register page renders inside the branded auth layout on the hosted instance', function () {
    bootInstance('hosted');

    $this->get('/app/register')
        ->assertOk()
        ->assertSee('Protege hoy lo que trasciende en el tiempo.')
        ->assertSee('Volver al inicio');
});

test('users can still sign in through the customized login page', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    Livewire::test(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'secret-password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});
