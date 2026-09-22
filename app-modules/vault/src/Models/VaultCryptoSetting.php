<?php

namespace Tequia\Vault\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tequia\Vault\Database\Factories\VaultCryptoSettingFactory;
use Tequia\Vault\Models\Concerns\BelongsToUser;

/**
 * One row per user (`user_id` unique): holds the Argon2id salt and KDF
 * parameters clients need to re-derive that user's vault encryption key.
 * None of this is secret — the server never sees the master password or
 * the key. `current()` relies on the `BelongsToUser` scope to resolve to
 * the authenticated user's own row.
 */
class VaultCryptoSetting extends Model
{
    use BelongsToUser;

    /** @use HasFactory<VaultCryptoSettingFactory> */
    use HasFactory;

    /**
     * OWASP-recommended Argon2id parameters, tuned for a WASM key
     * derivation running in the browser.
     */
    const DEFAULT_MEMORY_COST = 19456;

    const DEFAULT_ITERATIONS = 2;

    const DEFAULT_PARALLELISM = 1;

    const CURRENT_PROTOCOL_VERSION = 1;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'key_salt',
        'kdf_memory_cost',
        'kdf_iterations',
        'kdf_parallelism',
        'protocol_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kdf_memory_cost' => 'integer',
            'kdf_iterations' => 'integer',
            'kdf_parallelism' => 'integer',
            'protocol_version' => 'integer',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }

    /**
     * Generate a fresh random salt and persist the default KDF parameters.
     * Called once, the first time the vault is unlocked with no existing settings.
     */
    public static function bootstrap(): self
    {
        return static::create([
            'key_salt' => base64_encode(random_bytes(16)),
            'kdf_memory_cost' => self::DEFAULT_MEMORY_COST,
            'kdf_iterations' => self::DEFAULT_ITERATIONS,
            'kdf_parallelism' => self::DEFAULT_PARALLELISM,
            'protocol_version' => self::CURRENT_PROTOCOL_VERSION,
        ]);
    }

    protected static function newFactory(): VaultCryptoSettingFactory
    {
        return VaultCryptoSettingFactory::new();
    }
}
