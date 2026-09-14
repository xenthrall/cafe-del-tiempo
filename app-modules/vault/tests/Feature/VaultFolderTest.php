<?php

use Tequia\Vault\Models\VaultFolder;
use Tequia\Vault\Models\VaultItem;

it('stores an encrypted name as an opaque value', function () {
    $folder = VaultFolder::factory()->create([
        'encrypted_name' => 'opaque-cipher-blob',
    ]);

    expect($folder->fresh()->encrypted_name)->toBe('opaque-cipher-blob');
});

it('casts the payload schema version to an integer', function () {
    $folder = VaultFolder::factory()->create(['payload_schema_version' => 1]);

    expect($folder->payload_schema_version)->toBeInt();
});

it('has many vault items', function () {
    $folder = VaultFolder::factory()->create();
    VaultItem::factory()->count(2)->create(['folder_id' => $folder->id]);

    expect($folder->items)->toHaveCount(2);
});
