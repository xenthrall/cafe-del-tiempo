<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('creates a regular user from the prompts', function () {
    $this->artisan('user:create')
        ->expectsQuestion('Nombre', 'Ana')
        ->expectsQuestion('Correo electrónico', 'ana@example.com')
        ->expectsQuestion('Contraseña', 'secret-password')
        ->assertSuccessful();

    $user = User::firstWhere('email', 'ana@example.com');

    expect($user->name)->toBe('Ana')
        ->and($user->is_admin)->toBeFalse()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

test('the admin flag creates an administrator', function () {
    $this->artisan('user:create', ['--name' => 'Ana', '--email' => 'ana@example.com', '--admin' => true])
        ->expectsQuestion('Contraseña', 'secret-password')
        ->assertSuccessful();

    expect(User::firstWhere('email', 'ana@example.com')->is_admin)->toBeTrue();
});

test('rejects an email that is already registered', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->artisan('user:create', ['--name' => 'Ana', '--email' => 'ana@example.com'])
        ->expectsQuestion('Contraseña', 'secret-password')
        ->assertFailed();

    expect(User::where('email', 'ana@example.com')->count())->toBe(1);
});

test('rejects passwords shorter than eight characters', function () {
    $this->artisan('user:create', ['--name' => 'Ana', '--email' => 'ana@example.com'])
        ->expectsQuestion('Contraseña', 'short')
        ->assertFailed();

    expect(User::where('email', 'ana@example.com')->exists())->toBeFalse();
});
