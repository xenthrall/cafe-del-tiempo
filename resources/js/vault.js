import { argon2id } from 'hash-wasm';

/**
 * Café del Tiempo vault encryption protocol, version 1.
 *
 * Zero-knowledge: the server only ever sees the values produced by
 * `encryptValue()` below. The key is derived from the user's password with
 * Argon2id and never leaves the browser.
 *
 * Envelope shape (JSON, stored as-is in `encrypted_payload`/`encrypted_name`):
 *   { v: 1, iv: base64(12-byte AES-GCM IV), ct: base64(ciphertext + GCM tag) }
 */
const PROTOCOL_VERSION = 1;

function bytesToBase64(bytes) {
    let binary = '';

    for (let i = 0; i < bytes.length; i++) {
        binary += String.fromCharCode(bytes[i]);
    }

    return btoa(binary);
}

function base64ToBytes(base64) {
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes;
}

async function deriveVaultKey(password, cryptoSettings) {
    const derivedKeyBytes = await argon2id({
        password,
        salt: base64ToBytes(cryptoSettings.keySalt),
        parallelism: cryptoSettings.kdfParallelism,
        iterations: cryptoSettings.kdfIterations,
        memorySize: cryptoSettings.kdfMemoryCost,
        hashLength: 32,
        outputType: 'binary',
    });

    return crypto.subtle.importKey('raw', derivedKeyBytes, { name: 'AES-GCM' }, false, ['encrypt', 'decrypt']);
}

async function encryptValue(key, value) {
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const plaintext = new TextEncoder().encode(JSON.stringify(value));
    const ciphertext = await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, plaintext);

    return JSON.stringify({
        v: PROTOCOL_VERSION,
        iv: bytesToBase64(iv),
        ct: bytesToBase64(new Uint8Array(ciphertext)),
    });
}

async function decryptValue(key, envelope) {
    const { iv, ct } = JSON.parse(envelope);

    const plaintext = await crypto.subtle.decrypt(
        { name: 'AES-GCM', iv: base64ToBytes(iv) },
        key,
        base64ToBytes(ct),
    );

    return JSON.parse(new TextDecoder().decode(plaintext));
}

function emptyItemForm() {
    return {
        id: null,
        type: 'password',
        folderId: null,
        isFavorite: false,
        title: '',
        username: '',
        password: '',
        url: '',
        note: '',
        code: '',
        tags: '',
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('vaultApp', () => ({
        unlocked: false,
        unlocking: false,
        unlockError: null,
        password: '',
        key: null,

        decryptedFolders: [],
        decryptedItems: [],

        itemForm: emptyItemForm(),
        folderName: '',

        filterFolderId: null,
        search: '',

        init() {
            this.$wire.$watch('items', () => this.decryptAll());
            this.$wire.$watch('folders', () => this.decryptAll());
        },

        async unlock() {
            if (!this.password || this.unlocking) {
                return;
            }

            this.unlocking = true;
            this.unlockError = null;

            try {
                const key = await deriveVaultKey(this.password, this.$wire.cryptoSettings);

                this.decryptedFolders = await this.decryptFolders(key, this.$wire.folders);
                this.decryptedItems = await this.decryptItems(key, this.$wire.items);
                this.key = key;
                this.unlocked = true;
            } catch (error) {
                console.error('[vault] unlock failed', error);
                this.unlockError = 'No se pudo desbloquear la bóveda. Verifica tu contraseña.';
                this.key = null;
            } finally {
                this.password = '';
                this.unlocking = false;
            }
        },

        lock() {
            this.key = null;
            this.unlocked = false;
            this.decryptedFolders = [];
            this.decryptedItems = [];
        },

        async decryptFolders(key, folders) {
            return Promise.all(
                (folders ?? []).map(async (folder) => ({
                    id: folder.id,
                    name: await decryptValue(key, folder.encryptedName),
                })),
            );
        },

        async decryptItems(key, items) {
            return Promise.all(
                (items ?? []).map(async (item) => ({
                    id: item.id,
                    type: item.type,
                    folderId: item.folderId,
                    isFavorite: item.isFavorite,
                    createdAt: item.createdAt,
                    data: await decryptValue(key, item.encryptedPayload),
                })),
            );
        },

        async decryptAll() {
            if (!this.key) {
                return;
            }

            this.decryptedFolders = await this.decryptFolders(this.key, this.$wire.folders);
            this.decryptedItems = await this.decryptItems(this.key, this.$wire.items);
        },

        get visibleItems() {
            const search = this.search.trim().toLowerCase();

            return this.decryptedItems
                .filter((item) => this.filterFolderId === null || item.folderId === this.filterFolderId)
                .filter((item) => !search || (item.data.title ?? '').toLowerCase().includes(search));
        },

        openNewItemForm(type) {
            this.itemForm = emptyItemForm();
            this.itemForm.type = type ?? 'password';
            this.itemForm.folderId = this.filterFolderId;
            this.$dispatch('open-modal', { id: 'vault-item-form-modal' });
        },

        openEditItemForm(item) {
            this.itemForm = {
                id: item.id,
                type: item.type,
                folderId: item.folderId,
                isFavorite: item.isFavorite,
                title: item.data.title ?? '',
                username: item.data.username ?? '',
                password: item.data.password ?? '',
                url: item.data.url ?? '',
                note: item.data.note ?? '',
                code: item.data.code ?? '',
                tags: (item.data.tags ?? []).join(', '),
            };
            this.$dispatch('open-modal', { id: 'vault-item-form-modal' });
        },

        async submitItemForm() {
            if (!this.key || !this.itemForm.title) {
                return;
            }

            const payload = {
                title: this.itemForm.title,
                tags: this.itemForm.tags.split(',').map((tag) => tag.trim()).filter(Boolean),
            };

            if (this.itemForm.type === 'password') {
                payload.username = this.itemForm.username;
                payload.password = this.itemForm.password;
                payload.url = this.itemForm.url;
                payload.note = this.itemForm.note;
            } else if (this.itemForm.type === 'note') {
                payload.note = this.itemForm.note;
            } else if (this.itemForm.type === 'recovery_code') {
                payload.code = this.itemForm.code;
                payload.note = this.itemForm.note;
            }

            const encryptedPayload = await encryptValue(this.key, payload);

            if (this.itemForm.id) {
                await this.$wire.updateItem(
                    this.itemForm.id,
                    encryptedPayload,
                    this.itemForm.folderId,
                    this.itemForm.isFavorite,
                );
            } else {
                await this.$wire.createItem(
                    this.itemForm.type,
                    this.itemForm.folderId,
                    this.itemForm.isFavorite,
                    encryptedPayload,
                );
            }

            this.itemForm = emptyItemForm();
            this.$dispatch('close-modal', { id: 'vault-item-form-modal' });
        },

        async toggleFavorite(item) {
            await this.$wire.toggleFavorite(item.id);
        },

        async deleteItem(item) {
            if (!confirm('¿Eliminar este ítem?')) {
                return;
            }

            await this.$wire.deleteItem(item.id);
        },

        openNewFolderForm() {
            this.folderName = '';
            this.$dispatch('open-modal', { id: 'vault-folder-form-modal' });
        },

        async submitFolderForm() {
            if (!this.key || !this.folderName.trim()) {
                return;
            }

            const encryptedName = await encryptValue(this.key, this.folderName.trim());

            await this.$wire.createFolder(encryptedName);

            this.folderName = '';
            this.$dispatch('close-modal', { id: 'vault-folder-form-modal' });
        },
    }));
});
