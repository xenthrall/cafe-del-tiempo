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

const MIN_MASTER_PASSWORD_LENGTH = 8;

const GENERATED_PASSWORD_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*-_=+?';

function generatePassword(length = 20) {
    const randomValues = crypto.getRandomValues(new Uint32Array(length));

    return Array.from(randomValues, (value) => GENERATED_PASSWORD_ALPHABET[value % GENERATED_PASSWORD_ALPHABET.length]).join('');
}

function emptySetupForm() {
    return {
        password: '',
        passwordConfirmation: '',
        acknowledgedNoRecovery: false,
    };
}

/**
 * Known plaintext encrypted with the vault key and stored server-side. AES-GCM
 * fails identically for a wrong key and for a corrupted ciphertext, so
 * decrypting this first is what tells "wrong password" apart from "damaged item".
 */
const VERIFIER_PLAINTEXT = 'cafe-del-tiempo:vault-verifier:v1';

const WRONG_PASSWORD_MESSAGE = 'Contraseña incorrecta.';

/**
 * Never throws: a single damaged block must not take the rest of the vault down.
 */
async function tryDecryptValue(key, envelope) {
    try {
        return { ok: true, value: await decryptValue(key, envelope) };
    } catch (error) {
        console.error('[vault] block could not be decrypted', error);

        return { ok: false, value: null };
    }
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
        showPassword: false,
        setupForm: emptySetupForm(),
        key: null,

        decryptedFolders: [],
        decryptedItems: [],

        itemForm: emptyItemForm(),
        showItemFormPassword: false,
        folderName: '',
        pendingDeleteItem: null,

        filterFolderId: null,
        filterType: 'all',
        search: '',
        revealedItemIds: [],
        copiedKey: null,

        init() {
            this.$wire.$watch('items', () => this.decryptAll());
            this.$wire.$watch('folders', () => this.decryptAll());
        },

        /**
         * No verifier and nothing stored: the first password entered becomes the
         * vault's master password. An empty vault that already has a verifier is
         * not new — its password must still match.
         */
        get isNewVault() {
            return !this.$wire.cryptoSettings?.encryptedVerifier
                && (this.$wire.items ?? []).length === 0
                && (this.$wire.folders ?? []).length === 0;
        },

        get corruptedItemsCount() {
            return this.decryptedItems.filter((item) => item.isCorrupted).length;
        },

        get corruptedFoldersCount() {
            return this.decryptedFolders.filter((folder) => folder.isCorrupted).length;
        },

        get setupError() {
            const { password, passwordConfirmation } = this.setupForm;

            if (password && password.length < MIN_MASTER_PASSWORD_LENGTH) {
                return `La contraseña debe tener al menos ${MIN_MASTER_PASSWORD_LENGTH} caracteres.`;
            }

            if (passwordConfirmation && password !== passwordConfirmation) {
                return 'Las contraseñas no coinciden.';
            }

            return null;
        },

        get canCreateVault() {
            const { password, passwordConfirmation, acknowledgedNoRecovery } = this.setupForm;

            return !this.unlocking
                && password.length >= MIN_MASTER_PASSWORD_LENGTH
                && password === passwordConfirmation
                && acknowledgedNoRecovery;
        },

        async createVault() {
            if (!this.canCreateVault) {
                return;
            }

            this.password = this.setupForm.password;
            this.setupForm = emptySetupForm();

            await this.unlock();
        },

        async unlock() {
            if (!this.password || this.unlocking) {
                return;
            }

            this.unlocking = true;
            this.unlockError = null;

            try {
                const key = await deriveVaultKey(this.password, this.$wire.cryptoSettings);
                const encryptedVerifier = this.$wire.cryptoSettings.encryptedVerifier;
                const verifier = encryptedVerifier ? await tryDecryptValue(key, encryptedVerifier) : null;
                const isVerifierValid = verifier?.ok === true && verifier.value === VERIFIER_PLAINTEXT;

                const folders = await this.decryptFolders(key, this.$wire.folders);
                const items = await this.decryptItems(key, this.$wire.items);

                if (!isVerifierValid) {
                    // Missing or damaged verifier: fall back to the real blocks.
                    // A single one that decrypts proves the password is right.
                    const blocks = [...folders, ...items];
                    const hasReadableBlock = blocks.some((block) => !block.isCorrupted);
                    const isBrandNewVault = !encryptedVerifier && blocks.length === 0;

                    if (!hasReadableBlock && !isBrandNewVault) {
                        this.unlockError = blocks.length === 0
                            ? `${WRONG_PASSWORD_MESSAGE} Tu bóveda está vacía: si no recuerdas la contraseña, puedes reiniciarla sin perder nada.`
                            : WRONG_PASSWORD_MESSAGE;

                        return;
                    }

                    await this.$wire.storeVerifier(await encryptValue(key, VERIFIER_PLAINTEXT));
                }

                this.decryptedFolders = folders;
                this.decryptedItems = items;
                this.key = key;
                this.unlocked = true;
            } catch (error) {
                console.error('[vault] unlock failed', error);
                this.unlockError = 'No se pudo desbloquear la bóveda. Verifica tu contraseña.';
                this.key = null;
            } finally {
                this.password = '';
                this.showPassword = false;
                this.unlocking = false;
            }
        },

        lock() {
            this.key = null;
            this.unlocked = false;
            this.decryptedFolders = [];
            this.decryptedItems = [];
            this.filterFolderId = null;
            this.filterType = 'all';
            this.search = '';
            this.revealedItemIds = [];
            this.itemForm = emptyItemForm();
        },

        async decryptFolders(key, folders) {
            return Promise.all(
                (folders ?? []).map(async (folder) => {
                    const { ok, value } = await tryDecryptValue(key, folder.encryptedName);

                    return {
                        id: folder.id,
                        name: ok ? value : null,
                        isCorrupted: !ok,
                    };
                }),
            );
        },

        async decryptItems(key, items) {
            return Promise.all(
                (items ?? []).map(async (item) => {
                    const { ok, value } = await tryDecryptValue(key, item.encryptedPayload);

                    return {
                        id: item.id,
                        type: item.type,
                        folderId: item.folderId,
                        isFavorite: item.isFavorite,
                        createdAt: item.createdAt,
                        data: ok ? value : {},
                        isCorrupted: !ok,
                    };
                }),
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
                .filter((item) => {
                    if (this.filterType === 'all') {
                        return true;
                    }

                    return this.filterType === 'favorites' ? item.isFavorite : item.type === this.filterType;
                })
                .filter((item) => {
                    if (!search) {
                        return true;
                    }

                    return [item.data.title, item.data.username, item.data.url, ...(item.data.tags ?? [])]
                        .some((value) => (value ?? '').toLowerCase().includes(search));
                })
                .sort((a, b) => Number(b.isFavorite) - Number(a.isFavorite));
        },

        get favoritesCount() {
            return this.decryptedItems.filter((item) => item.isFavorite).length;
        },

        get hasActiveFilters() {
            return this.filterFolderId !== null || this.filterType !== 'all' || this.search.trim() !== '';
        },

        clearFilters() {
            this.filterFolderId = null;
            this.filterType = 'all';
            this.search = '';
        },

        folderItemsCount(folderId) {
            return this.decryptedItems.filter((item) => item.folderId === folderId).length;
        },

        folderLabel(folderId) {
            const folder = this.decryptedFolders.find((folder) => folder.id === folderId);

            if (!folder) {
                return null;
            }

            return folder.isCorrupted ? 'Carpeta ilegible' : folder.name;
        },

        isRevealed(item) {
            return this.revealedItemIds.includes(item.id);
        },

        toggleReveal(item) {
            this.revealedItemIds = this.isRevealed(item)
                ? this.revealedItemIds.filter((id) => id !== item.id)
                : [...this.revealedItemIds, item.id];
        },

        async copy(value, copiedKey) {
            if (!value) {
                return;
            }

            await navigator.clipboard.writeText(value);

            this.copiedKey = copiedKey;

            setTimeout(() => {
                if (this.copiedKey === copiedKey) {
                    this.copiedKey = null;
                }
            }, 1500);
        },

        safeUrl(url) {
            if (!url) {
                return null;
            }

            const normalizedUrl = /^https?:\/\//i.test(url) ? url : `https://${url}`;

            try {
                return new URL(normalizedUrl).href;
            } catch {
                return null;
            }
        },

        generateItemPassword() {
            this.itemForm.password = generatePassword();
            this.showItemFormPassword = true;
        },

        openNewItemForm(type) {
            this.itemForm = emptyItemForm();
            this.showItemFormPassword = false;
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
            this.showItemFormPassword = false;
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

            const folderId = this.itemForm.folderId ? Number(this.itemForm.folderId) : null;

            if (this.itemForm.id) {
                await this.$wire.updateItem(
                    this.itemForm.id,
                    encryptedPayload,
                    folderId,
                    this.itemForm.isFavorite,
                );
            } else {
                await this.$wire.createItem(
                    this.itemForm.type,
                    folderId,
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

        confirmDeleteItem(item) {
            this.pendingDeleteItem = item;
            this.$dispatch('open-modal', { id: 'vault-delete-item-modal' });
        },

        async deleteItem() {
            if (!this.pendingDeleteItem) {
                return;
            }

            await this.$wire.deleteItem(this.pendingDeleteItem.id);

            this.pendingDeleteItem = null;
            this.$dispatch('close-modal', { id: 'vault-delete-item-modal' });
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
