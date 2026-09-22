<?php

namespace Tequia\Vault\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tequia\Vault\Database\Factories\VaultFolderFactory;
use Tequia\Vault\Models\Concerns\BelongsToUser;

class VaultFolder extends Model
{
    use BelongsToUser;

    /** @use HasFactory<VaultFolderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'encrypted_name',
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
     * @return HasMany<VaultItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(VaultItem::class, 'folder_id');
    }

    protected static function newFactory(): VaultFolderFactory
    {
        return VaultFolderFactory::new();
    }
}
