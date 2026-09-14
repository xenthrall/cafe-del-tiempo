<?php

namespace Tequia\Vault\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tequia\Vault\Database\Factories\VaultItemFactory;
use Tequia\Vault\Enums\VaultItemType;

class VaultItem extends Model
{
    /** @use HasFactory<VaultItemFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'folder_id',
        'is_favorite',
        'encrypted_payload',
        'payload_schema_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VaultItemType::class,
            'is_favorite' => 'boolean',
            'payload_schema_version' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<VaultFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(VaultFolder::class, 'folder_id');
    }

    /**
     * @return HasMany<VaultItemVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(VaultItemVersion::class);
    }

    protected static function newFactory(): VaultItemFactory
    {
        return VaultItemFactory::new();
    }
}
