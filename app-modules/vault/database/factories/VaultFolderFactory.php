<?php

namespace Tequia\Vault\Database\Factories;

use App\Models\User;
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
            'user_id' => User::factory(),
            'encrypted_name' => base64_encode(fake()->words(2, true)),
            'payload_schema_version' => 1,
        ];
    }
}
