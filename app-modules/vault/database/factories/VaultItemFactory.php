<?php

namespace Tequia\Vault\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Vault\Enums\VaultItemType;
use Tequia\Vault\Models\VaultItem;

/**
 * @extends Factory<VaultItem>
 */
class VaultItemFactory extends Factory
{
    protected $model = VaultItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(VaultItemType::cases()),
            'folder_id' => null,
            'is_favorite' => false,
            'encrypted_payload' => base64_encode(fake()->sentence()),
            'payload_schema_version' => 1,
        ];
    }
}
