<?php

use Tequia\Vault\Enums\VaultItemType;
use Tequia\Vault\Models\VaultFolder;
use Tequia\Vault\Models\VaultItem;

it('casts type to a VaultItemType enum', function () {
    $item = VaultItem::factory()->create(['type' => VaultItemType::Password]);

    expect($item->fresh()->type)->toBe(VaultItemType::Password);
});

it('casts is_favorite to a boolean', function () {
    $item = VaultItem::factory()->create(['is_favorite' => 1]);

    expect($item->fresh()->is_favorite)->toBeTrue();
});

it('stores the encrypted payload as an opaque value', function () {
    $item = VaultItem::factory()->create(['encrypted_payload' => 'opaque-cipher-blob']);

    expect($item->fresh()->encrypted_payload)->toBe('opaque-cipher-blob');
});

it('belongs to a folder', function () {
    $folder = VaultFolder::factory()->create();
    $item = VaultItem::factory()->create(['folder_id' => $folder->id]);

    expect($item->folder)->toBeInstanceOf(VaultFolder::class)
        ->and($item->folder->id)->toBe($folder->id);
});

it('can exist without a folder', function () {
    $item = VaultItem::factory()->create(['folder_id' => null]);

    expect($item->folder)->toBeNull();
});

it('is soft deleted instead of removed permanently', function () {
    $item = VaultItem::factory()->create();

    $item->delete();

    $this->assertSoftDeleted($item);
});
