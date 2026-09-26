<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Vault\Enums\VaultItemType;
use Tequia\Vault\Filament\Pages\VaultDashboard;
use Tequia\Vault\Models\VaultCryptoSetting;
use Tequia\Vault\Models\VaultFolder;
use Tequia\Vault\Models\VaultItem;
use Tequia\Vault\Models\VaultItemVersion;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(VaultDashboard::getUrl())->assertSuccessful();
});

it('bootstraps crypto settings the first time the page loads', function () {
    expect(VaultCryptoSetting::current())->toBeNull();

    Livewire::test(VaultDashboard::class);

    expect(VaultCryptoSetting::current())->not->toBeNull();
});

it('stores the password verifier the first time', function () {
    Livewire::test(VaultDashboard::class)
        ->call('storeVerifier', 'verifier-blob')
        ->assertHasNoErrors()
        ->assertSet('cryptoSettings.encryptedVerifier', 'verifier-blob');

    expect(VaultCryptoSetting::current()->encrypted_verifier)->toBe('verifier-blob');
});

it('replaces a damaged password verifier', function () {
    VaultCryptoSetting::factory()->create([
        'user_id' => auth()->id(),
        'encrypted_verifier' => 'damaged-verifier',
    ]);

    Livewire::test(VaultDashboard::class)
        ->call('storeVerifier', 'repaired-verifier')
        ->assertSet('cryptoSettings.encryptedVerifier', 'repaired-verifier');

    expect(VaultCryptoSetting::current()->encrypted_verifier)->toBe('repaired-verifier');
});

it('creates a folder from an already-encrypted name', function () {
    Livewire::test(VaultDashboard::class)
        ->call('createFolder', 'opaque-cipher-blob')
        ->assertHasNoErrors();

    expect(VaultFolder::query()->where('encrypted_name', 'opaque-cipher-blob')->exists())->toBeTrue();
});

it('creates an item from an already-encrypted payload', function () {
    $folder = VaultFolder::factory()->create();

    Livewire::test(VaultDashboard::class)
        ->call('createItem', 'password', $folder->id, true, 'opaque-cipher-blob')
        ->assertHasNoErrors();

    $item = VaultItem::query()->where('encrypted_payload', 'opaque-cipher-blob')->sole();

    expect($item->type)->toBe(VaultItemType::Password)
        ->and($item->folder_id)->toBe($folder->id)
        ->and($item->is_favorite)->toBeTrue();
});

it('rejects an item with an invalid type', function () {
    Livewire::test(VaultDashboard::class)
        ->call('createItem', 'not-a-real-type', null, false, 'opaque-cipher-blob')
        ->assertHasErrors(['type']);

    expect(VaultItem::query()->count())->toBe(0);
});

it('snapshots the previous payload into a version before updating an item', function () {
    $item = VaultItem::factory()->create(['encrypted_payload' => 'old-blob']);

    Livewire::test(VaultDashboard::class)
        ->call('updateItem', $item->id, 'new-blob', null, false)
        ->assertHasNoErrors();

    expect($item->fresh()->encrypted_payload)->toBe('new-blob')
        ->and(VaultItemVersion::query()->where('vault_item_id', $item->id)->where('encrypted_payload', 'old-blob')->exists())->toBeTrue();
});

it('toggles the favorite flag', function () {
    $item = VaultItem::factory()->create(['is_favorite' => false]);

    Livewire::test(VaultDashboard::class)->call('toggleFavorite', $item->id);

    expect($item->fresh()->is_favorite)->toBeTrue();
});

it('soft deletes an item', function () {
    $item = VaultItem::factory()->create();

    Livewire::test(VaultDashboard::class)->call('deleteItem', $item->id);

    $this->assertSoftDeleted($item);
});

it('resets the vault when the user types the confirmation word', function () {
    Livewire::test(VaultDashboard::class)->call('storeVerifier', 'verifier-blob');
    $originalSalt = VaultCryptoSetting::current()->key_salt;

    $item = VaultItem::factory()->create(['user_id' => auth()->id()]);
    VaultItemVersion::factory()->create(['user_id' => auth()->id(), 'vault_item_id' => $item->id]);
    VaultItem::factory()->create(['user_id' => auth()->id()])->delete();
    VaultFolder::factory()->create(['user_id' => auth()->id()]);

    Livewire::test(VaultDashboard::class)
        ->mountAction('resetVault')
        ->set('mountedActions.0.data.confirmation', VaultDashboard::RESET_CONFIRMATION_WORD)
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertDispatched('vault-reset')
        ->assertSet('items', [])
        ->assertSet('folders', []);

    expect(VaultItem::withTrashed()->count())->toBe(0)
        ->and(VaultItemVersion::query()->count())->toBe(0)
        ->and(VaultFolder::query()->count())->toBe(0)
        ->and(VaultCryptoSetting::current()->key_salt)->not->toBe($originalSalt)
        ->and(VaultCryptoSetting::current()->encrypted_verifier)->toBeNull();
});

it('does not reset the vault without the exact confirmation word', function () {
    VaultItem::factory()->create(['user_id' => auth()->id()]);

    Livewire::test(VaultDashboard::class)
        ->mountAction('resetVault')
        ->set('mountedActions.0.data.confirmation', 'ELIMINAR')
        ->callMountedAction()
        ->assertHasActionErrors(['confirmation' => 'in']);

    expect(VaultItem::query()->count())->toBe(1);
});

it('only resets the authenticated user vault', function () {
    $otherUserItem = VaultItem::factory()->create();

    Livewire::test(VaultDashboard::class)
        ->mountAction('resetVault')
        ->set('mountedActions.0.data.confirmation', VaultDashboard::RESET_CONFIRMATION_WORD)
        ->callMountedAction();

    expect(VaultItem::withoutGlobalScopes()->whereKey($otherUserItem->id)->exists())->toBeTrue();
});
