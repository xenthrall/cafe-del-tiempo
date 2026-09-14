<?php

namespace Tequia\Vault\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Vault\Models\VaultFolder;

/**
 * @extends Factory<VaultFolder>
 */
class VaultFolderFactory extends Factory
{
    protected $model = VaultFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'encrypted_name' => base64_encode(fake()->words(2, true)),
            'payload_schema_version' => 1,
        ];
    }
}
