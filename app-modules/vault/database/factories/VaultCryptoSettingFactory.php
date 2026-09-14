<?php

namespace Tequia\Vault\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Vault\Models\VaultCryptoSetting;

/**
 * @extends Factory<VaultCryptoSetting>
 */
class VaultCryptoSettingFactory extends Factory
{
    protected $model = VaultCryptoSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key_salt' => base64_encode(random_bytes(16)),
            'kdf_memory_cost' => VaultCryptoSetting::DEFAULT_MEMORY_COST,
            'kdf_iterations' => VaultCryptoSetting::DEFAULT_ITERATIONS,
            'kdf_parallelism' => VaultCryptoSetting::DEFAULT_PARALLELISM,
            'protocol_version' => VaultCryptoSetting::CURRENT_PROTOCOL_VERSION,
        ];
    }
}
