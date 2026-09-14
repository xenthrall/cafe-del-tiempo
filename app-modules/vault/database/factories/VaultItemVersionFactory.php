<?php

namespace Tequia\Vault\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Vault\Models\VaultItem;
use Tequia\Vault\Models\VaultItemVersion;

/**
 * @extends Factory<VaultItemVersion>
 */
class VaultItemVersionFactory extends Factory
{
    protected $model = VaultItemVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_item_id' => VaultItem::factory(),
            'encrypted_payload' => base64_encode(fake()->sentence()),
            'payload_schema_version' => 1,
        ];
    }
}
