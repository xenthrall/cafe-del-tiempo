<x-filament-panels::page>
    @vite(['resources/js/vault.js'])

    <div
        x-data="vaultApp()"
        x-cloak
    >
        {{-- Locked state --}}
        <div
            x-show="!unlocked"
            class="mx-auto flex max-w-md flex-col items-center gap-4 py-16 text-center"
        >
            <x-filament::icon
                icon="heroicon-o-lock-closed"
                class="h-10 w-10 text-gray-400 dark:text-gray-500"
            />

            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                Desbloquea tu bóveda
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ingresa tu contraseña para descifrar tus credenciales en este navegador. Nunca se envía al servidor.
            </p>

            <form
                x-on:submit.prevent="unlock"
                class="flex w-full flex-col gap-3"
            >
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="password"
                        x-model="password"
                        placeholder="Tu contraseña"
                        autocomplete="current-password"
                    />
                </x-filament::input.wrapper>

                <x-filament::button
                    type="submit"
                    icon="heroicon-o-lock-open"
                    x-bind:disabled="unlocking || !password"
                >
                    <span x-show="!unlocking">Desbloquear</span>
                    <span x-show="unlocking">Descifrando…</span>
                </x-filament::button>

                <p
                    x-show="unlockError"
                    x-text="unlockError"
                    class="text-sm text-danger-600 dark:text-danger-400"
                ></p>
            </form>
        </div>

        {{-- Unlocked state --}}
        <div
            x-show="unlocked"
            class="flex flex-col gap-6 lg:flex-row"
        >
            {{-- Folders sidebar --}}
            <aside class="flex shrink-0 flex-col gap-2 lg:w-56">
                <button
                    type="button"
                    x-on:click="filterFolderId = null"
                    x-bind:class="filterFolderId === null ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
                    class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium"
                >
                    Todos los ítems
                    <span x-text="decryptedItems.length"></span>
                </button>

                <template x-for="folder in decryptedFolders" x-bind:key="folder.id">
                    <button
                        type="button"
                        x-on:click="filterFolderId = folder.id"
                        x-bind:class="filterFolderId === folder.id ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium"
                    >
                        <span x-text="folder.name" class="truncate"></span>
                    </button>
                </template>

                <x-filament::button
                    color="gray"
                    icon="heroicon-o-folder-plus"
                    x-on:click="openNewFolderForm"
                    class="mt-2"
                >
                    Nueva carpeta
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    icon="heroicon-o-lock-closed"
                    x-on:click="lock"
                >
                    Bloquear bóveda
                </x-filament::button>
            </aside>

            {{-- Items --}}
            <div class="flex min-w-0 flex-1 flex-col gap-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <x-filament::input.wrapper class="sm:max-w-xs">
                        <x-filament::input
                            type="search"
                            x-model="search"
                            placeholder="Buscar por título…"
                        />
                    </x-filament::input.wrapper>

                    <div class="flex flex-wrap gap-2">
                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-key"
                            x-on:click="openNewItemForm('password')"
                        >
                            Contraseña
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-document-text"
                            x-on:click="openNewItemForm('note')"
                        >
                            Nota
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-shield-check"
                            x-on:click="openNewItemForm('recovery_code')"
                        >
                            Código de recuperación
                        </x-filament::button>
                    </div>
                </div>

                <p
                    x-show="decryptedItems.length === 0"
                    class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    Tu bóveda está vacía. Agrega tu primera credencial arriba.
                </p>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="item in visibleItems" x-bind:key="item.id">
                        <div class="flex flex-col gap-2 rounded-xl border border-gray-200 p-4 dark:border-white/10">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <x-filament::icon
                                        x-show="item.type === 'password'"
                                        icon="heroicon-o-key"
                                        class="h-4 w-4 shrink-0 text-gray-400"
                                    />
                                    <x-filament::icon
                                        x-show="item.type === 'note'"
                                        icon="heroicon-o-document-text"
                                        class="h-4 w-4 shrink-0 text-gray-400"
                                    />
                                    <x-filament::icon
                                        x-show="item.type === 'recovery_code'"
                                        icon="heroicon-o-shield-check"
                                        class="h-4 w-4 shrink-0 text-gray-400"
                                    />
                                    <span x-text="item.data.title" class="truncate font-medium text-gray-950 dark:text-white"></span>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="toggleFavorite(item)"
                                    class="shrink-0 text-gray-400 hover:text-warning-500"
                                >
                                    <x-filament::icon
                                        x-show="item.isFavorite"
                                        icon="heroicon-s-star"
                                        class="h-4 w-4 text-warning-500"
                                    />
                                    <x-filament::icon
                                        x-show="!item.isFavorite"
                                        icon="heroicon-o-star"
                                        class="h-4 w-4"
                                    />
                                </button>
                            </div>

                            <p
                                x-show="item.type === 'password'"
                                x-text="item.data.username"
                                class="truncate text-sm text-gray-500 dark:text-gray-400"
                            ></p>

                            <p
                                x-show="item.type === 'note' || item.type === 'recovery_code'"
                                x-text="item.type === 'note' ? item.data.note : item.data.code"
                                class="line-clamp-2 text-sm text-gray-500 dark:text-gray-400"
                            ></p>

                            <div class="mt-auto flex items-center justify-end gap-2 pt-2">
                                <x-filament::icon-button
                                    icon="heroicon-o-pencil-square"
                                    label="Editar"
                                    x-on:click="openEditItemForm(item)"
                                />
                                <x-filament::icon-button
                                    icon="heroicon-o-trash"
                                    label="Eliminar"
                                    color="danger"
                                    x-on:click="deleteItem(item)"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Item form modal --}}
        <x-filament::modal id="vault-item-form-modal" width="lg">
            <x-slot name="heading">
                <span x-text="itemForm.id ? 'Editar ítem' : 'Nuevo ítem'"></span>
            </x-slot>

            <form
                x-on:submit.prevent="submitItemForm"
                class="flex flex-col gap-4"
            >
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            x-model="itemForm.title"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>

                <template x-if="itemForm.type === 'password'">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuario</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" x-model="itemForm.username" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" x-model="itemForm.password" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">URL</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" x-model="itemForm.url" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </template>

                <template x-if="itemForm.type === 'recovery_code'">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" x-model="itemForm.code" />
                        </x-filament::input.wrapper>
                    </div>
                </template>

                <template x-if="itemForm.type === 'note' || itemForm.type === 'password' || itemForm.type === 'recovery_code'">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                        <x-filament::input.wrapper>
                            <textarea
                                x-model="itemForm.note"
                                rows="3"
                                class="fi-input block w-full border-none bg-transparent p-0 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500"
                            ></textarea>
                        </x-filament::input.wrapper>
                    </div>
                </template>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Etiquetas (separadas por coma)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" x-model="itemForm.tags" />
                    </x-filament::input.wrapper>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <x-filament::input.checkbox x-model="itemForm.isFavorite" />
                    Favorito
                </label>

                <x-filament::button type="submit">
                    Guardar
                </x-filament::button>
            </form>
        </x-filament::modal>

        {{-- Folder form modal --}}
        <x-filament::modal id="vault-folder-form-modal">
            <x-slot name="heading">
                Nueva carpeta
            </x-slot>

            <form
                x-on:submit.prevent="submitFolderForm"
                class="flex flex-col gap-4"
            >
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            x-model="folderName"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>

                <x-filament::button type="submit">
                    Crear carpeta
                </x-filament::button>
            </form>
        </x-filament::modal>
    </div>
</x-filament-panels::page>
