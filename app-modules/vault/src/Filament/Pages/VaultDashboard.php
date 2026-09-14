<?php

namespace Tequia\Vault\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Tequia\Vault\Enums\VaultItemType;
use Tequia\Vault\Models\VaultCryptoSetting;
use Tequia\Vault\Models\VaultFolder;
use Tequia\Vault\Models\VaultItem;
use Tequia\Vault\Models\VaultItemVersion;

class VaultDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static ?string $navigationLabel = 'Bóveda';

    protected static ?string $title = 'Bóveda';

    protected string $view = 'vault::filament.pages.vault-dashboard';

    /**
     * Not secret: the client needs these to re-derive the vault key from the
     * master password. The server never sees the password or the key itself.
     *
     * @var array<string, mixed>
     */
    public array $cryptoSettings = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $folders = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->refreshCryptoSettings();
        $this->refreshFolders();
        $this->refreshItems();
    }

    public function createFolder(string $encryptedName): void
    {
        Validator::make(
            ['encrypted_name' => $encryptedName],
            ['encrypted_name' => ['required', 'string']],
        )->validate();

        VaultFolder::create([
            'encrypted_name' => $encryptedName,
            'payload_schema_version' => VaultCryptoSetting::CURRENT_PROTOCOL_VERSION,
        ]);

        $this->refreshFolders();
    }

    public function createItem(string $type, ?int $folderId, bool $isFavorite, string $encryptedPayload): void
    {
        $data = Validator::make(
            [
                'type' => $type,
                'folder_id' => $folderId,
                'encrypted_payload' => $encryptedPayload,
            ],
            [
                'type' => ['required', 'string', 'in:'.implode(',', array_column(VaultItemType::cases(), 'value'))],
                'folder_id' => ['nullable', 'integer', 'exists:vault_folders,id'],
                'encrypted_payload' => ['required', 'string'],
            ],
        )->validate();

        VaultItem::create([
            'type' => $data['type'],
            'folder_id' => $data['folder_id'],
            'is_favorite' => $isFavorite,
            'encrypted_payload' => $data['encrypted_payload'],
            'payload_schema_version' => VaultCryptoSetting::CURRENT_PROTOCOL_VERSION,
        ]);

        $this->refreshItems();
    }

    public function updateItem(int $itemId, string $encryptedPayload, ?int $folderId, bool $isFavorite): void
    {
        $data = Validator::make(
            [
                'item_id' => $itemId,
                'folder_id' => $folderId,
                'encrypted_payload' => $encryptedPayload,
            ],
            [
                'item_id' => ['required', 'integer', 'exists:vault_items,id'],
                'folder_id' => ['nullable', 'integer', 'exists:vault_folders,id'],
                'encrypted_payload' => ['required', 'string'],
            ],
        )->validate();

        $item = VaultItem::findOrFail($data['item_id']);

        VaultItemVersion::create([
            'vault_item_id' => $item->id,
            'encrypted_payload' => $item->encrypted_payload,
            'payload_schema_version' => $item->payload_schema_version,
        ]);

        $item->update([
            'encrypted_payload' => $data['encrypted_payload'],
            'folder_id' => $data['folder_id'],
            'is_favorite' => $isFavorite,
        ]);

        $this->refreshItems();
    }

    public function toggleFavorite(int $itemId): void
    {
        $item = VaultItem::findOrFail($itemId);

        $item->update(['is_favorite' => ! $item->is_favorite]);

        $this->refreshItems();
    }

    public function deleteItem(int $itemId): void
    {
        VaultItem::findOrFail($itemId)->delete();

        $this->refreshItems();
    }

    private function refreshCryptoSettings(): void
    {
        $settings = VaultCryptoSetting::current() ?? VaultCryptoSetting::bootstrap();

        $this->cryptoSettings = [
            'keySalt' => $settings->key_salt,
            'kdfMemoryCost' => $settings->kdf_memory_cost,
            'kdfIterations' => $settings->kdf_iterations,
            'kdfParallelism' => $settings->kdf_parallelism,
            'protocolVersion' => $settings->protocol_version,
        ];
    }

    private function refreshFolders(): void
    {
        $this->folders = VaultFolder::query()
            ->orderBy('id')
            ->get()
            ->map(fn (VaultFolder $folder): array => $this->serializeFolder($folder))
            ->all();
    }

    private function refreshItems(): void
    {
        $this->items = VaultItem::query()
            ->latest()
            ->get()
            ->map(fn (VaultItem $item): array => $this->serializeItem($item))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeFolder(VaultFolder $folder): array
    {
        return [
            'id' => $folder->id,
            'encryptedName' => $folder->encrypted_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeItem(VaultItem $item): array
    {
        return [
            'id' => $item->id,
            'type' => $item->type->value,
            'folderId' => $item->folder_id,
            'isFavorite' => $item->is_favorite,
            'encryptedPayload' => $item->encrypted_payload,
            'createdAt' => $item->created_at?->toIso8601String(),
        ];
    }
}
