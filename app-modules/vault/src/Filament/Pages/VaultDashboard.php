<?php

namespace Tequia\Vault\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tequia\Vault\Enums\VaultItemType;
use Tequia\Vault\Models\VaultCryptoSetting;
use Tequia\Vault\Models\VaultFolder;
use Tequia\Vault\Models\VaultItem;
use Tequia\Vault\Models\VaultItemVersion;

/**
 * Bóveda zero-knowledge: todo se cifra/descifra en el navegador (ver
 * resources/js/vault.js). Como la contraseña maestra nunca llega al
 * servidor, no hay forma de recuperarla: si el usuario la olvida, la única
 * salida es `resetVaultAction()`, que borra todo y genera una sal nueva.
 */
class VaultDashboard extends Page
{
    /**
     * Word the user must type (GitHub-style) to confirm a vault reset.
     */
    const RESET_CONFIRMATION_WORD = 'eliminar';

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

    /**
     * Stores (or replaces) the password verifier. The server can't check the
     * password itself, so the client only calls this once it has proven the
     * key is right: a brand-new vault, or at least one real item/folder that
     * decrypted — which is also how a damaged verifier gets repaired.
     */
    public function storeVerifier(string $encryptedVerifier): void
    {
        Validator::make(
            ['encrypted_verifier' => $encryptedVerifier],
            ['encrypted_verifier' => ['required', 'string']],
        )->validate();

        VaultCryptoSetting::query()->update(['encrypted_verifier' => $encryptedVerifier]);

        $this->refreshCryptoSettings();
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
                'folder_id' => ['nullable', 'integer', Rule::exists('vault_folders', 'id')->where('user_id', auth()->id())],
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
                'item_id' => ['required', 'integer', Rule::exists('vault_items', 'id')->where('user_id', auth()->id())],
                'folder_id' => ['nullable', 'integer', Rule::exists('vault_folders', 'id')->where('user_id', auth()->id())],
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

    /**
     * Permanently wipes every item (including soft-deleted ones and their
     * versions), folder and the KDF salt, so the user can start over with
     * a new master password. Irreversible by design.
     */
    public function resetVaultAction(): Action
    {
        return Action::make('resetVault')
            ->label('Reiniciar bóveda')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->link()
            ->modalIcon(Heroicon::OutlinedExclamationTriangle)
            ->modalIconColor('danger')
            ->modalHeading('Reiniciar bóveda')
            ->modalDescription('Se eliminarán de forma permanente todas tus contraseñas, notas, códigos de recuperación y carpetas. Como tu contraseña nunca sale de tu navegador, no existe ninguna forma de recuperar estos datos. Después podrás crear una contraseña nueva.')
            ->modalSubmitActionLabel('Entiendo, eliminar todo')
            ->modalWidth('md')
            ->schema([
                TextInput::make('confirmation')
                    ->label('Para confirmar, escribe «'.self::RESET_CONFIRMATION_WORD.'»')
                    ->placeholder(self::RESET_CONFIRMATION_WORD)
                    ->autocomplete(false)
                    ->required()
                    ->in([self::RESET_CONFIRMATION_WORD])
                    ->validationMessages([
                        'in' => 'Escribe exactamente «'.self::RESET_CONFIRMATION_WORD.'» para confirmar.',
                    ]),
            ])
            ->action(function (): void {
                DB::transaction(function (): void {
                    // Builder::forceDelete() skips global scopes, so the
                    // BelongsToUser scope must be restated explicitly here.
                    VaultItem::query()->withTrashed()->where('user_id', auth()->id())->forceDelete();
                    VaultFolder::query()->delete();
                    VaultCryptoSetting::query()->delete();
                });

                $this->refreshCryptoSettings();
                $this->refreshFolders();
                $this->refreshItems();

                $this->dispatch('vault-reset');

                Notification::make()
                    ->title('Bóveda reiniciada')
                    ->body('Crea una contraseña nueva para empezar de cero.')
                    ->success()
                    ->send();
            });
    }

    private function refreshCryptoSettings(): void
    {
        $settings = VaultCryptoSetting::current() ?? VaultCryptoSetting::bootstrap();

        $this->cryptoSettings = [
            'keySalt' => $settings->key_salt,
            'encryptedVerifier' => $settings->encrypted_verifier,
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
