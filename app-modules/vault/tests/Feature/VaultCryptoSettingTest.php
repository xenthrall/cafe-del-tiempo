<?php

use App\Models\User;
use Tequia\Vault\Models\VaultCryptoSetting;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('returns null when no crypto settings exist yet', function () {
    expect(VaultCryptoSetting::current())->toBeNull();
});

it('returns the existing settings row', function () {
    $settings = VaultCryptoSetting::factory()->create();

    expect(VaultCryptoSetting::current()->id)->toBe($settings->id);
});

it('bootstraps a random salt and the default KDF parameters', function () {
    $settings = VaultCryptoSetting::bootstrap();

    expect($settings->key_salt)->not->toBeEmpty()
        ->and(base64_decode($settings->key_salt, strict: true))->not->toBeFalse()
        ->and($settings->kdf_memory_cost)->toBe(VaultCryptoSetting::DEFAULT_MEMORY_COST)
        ->and($settings->kdf_iterations)->toBe(VaultCryptoSetting::DEFAULT_ITERATIONS)
        ->and($settings->kdf_parallelism)->toBe(VaultCryptoSetting::DEFAULT_PARALLELISM)
        ->and($settings->protocol_version)->toBe(VaultCryptoSetting::CURRENT_PROTOCOL_VERSION);
});

it('generates a different salt on each bootstrap', function () {
    $first = VaultCryptoSetting::bootstrap();
    $first->delete();

    $second = VaultCryptoSetting::bootstrap();

    expect($second->key_salt)->not->toBe($first->key_salt);
});
