<?php

use Tequia\Vault\Models\VaultItem;
use Tequia\Vault\Models\VaultItemVersion;

it('belongs to a vault item', function () {
    $item = VaultItem::factory()->create();
    $version = VaultItemVersion::factory()->create(['vault_item_id' => $item->id]);

    expect($version->item)->toBeInstanceOf(VaultItem::class)
        ->and($version->item->id)->toBe($item->id);
});

it('is accessible from the vault item as a version history', function () {
    $item = VaultItem::factory()->create();
    VaultItemVersion::factory()->count(3)->create(['vault_item_id' => $item->id]);

    expect($item->versions)->toHaveCount(3);
});

it('is deleted when the parent vault item is deleted permanently', function () {
    $item = VaultItem::factory()->create();
    $version = VaultItemVersion::factory()->create(['vault_item_id' => $item->id]);

    $item->forceDelete();

    $this->assertModelMissing($version);
});
