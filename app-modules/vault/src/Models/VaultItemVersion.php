<?php

namespace Tequia\Vault\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tequia\Vault\Database\Factories\VaultItemVersionFactory;
use Tequia\Vault\Models\Concerns\BelongsToUser;

class VaultItemVersion extends Model
{
    use BelongsToUser;

    /** @use HasFactory<VaultItemVersionFactory> */
    use HasFactory;

    /**
     * Version snapshots are immutable once written.
     */
    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'vault_item_id',
        'encrypted_payload',
        'payload_schema_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload_schema_version' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<VaultItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(VaultItem::class, 'vault_item_id');
    }

    protected static function newFactory(): VaultItemVersionFactory
    {
        return VaultItemVersionFactory::new();
    }
}
