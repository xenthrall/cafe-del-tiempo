<x-filament-panels::page>
    @vite(['resources/js/vault.js'])

    <div
        x-data="vaultApp()"
        x-on:vault-reset.window="lock()"
        x-cloak
    >
        {{-- Locked state --}}
        <div
            x-show="!unlocked"
            class="mx-auto w-full max-w-md py-8 sm:py-12"
        >
            <x-filament::section>
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <x-filament::icon
                            icon="heroicon-o-lock-closed"
                            class="h-7 w-7 text-primary-600 dark:text-primary-400"
                        />
                    </div>

                    <h2
                        class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white"
                        x-text="isNewVault ? 'Crea tu bóveda' : 'Desbloquea tu bóveda'"
                    ></h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                        x-text="isNewVault
                            ? 'Elige una contraseña maestra. Con ella se cifra todo en este navegador; nunca se envía al servidor.'
                            : 'Ingresa tu contraseña maestra para descifrar tus datos en este navegador. Nunca se envía al servidor.'"
                    ></p>
                </div>

                {{-- No-recovery warning --}}
                <div class="mt-6 flex gap-3 rounded-lg bg-warning-50 p-3 text-sm ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:ring-warning-400/30">
                    <x-filament::icon
                        icon="heroicon-m-exclamation-triangle"
                        class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400"
                    />
                    <div class="flex flex-col gap-1 text-warning-800 dark:text-warning-200">
                        <p class="font-semibold">Tu contraseña no se puede recuperar</p>
                        <p>
                            Nadie, ni siquiera nosotros, puede ver ni restablecer tu contraseña maestra.
                            Si la olvidas o la pierdes, <strong>no será posible recuperar los datos de tu bóveda</strong>;
                            solo podrás reiniciarla y empezar de cero.
                        </p>
                    </div>
                </div>

                {{-- Create vault (first time) --}}
                <form
                    x-show="isNewVault"
                    x-on:submit.prevent="createVault"
                    class="mt-6 flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-new-password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña maestra</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="vault-new-password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="setupForm.password"
                                autocomplete="new-password"
                            />
                            <x-slot name="suffix">
                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                    x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                >
                                    <x-filament::icon x-show="!showPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                    <x-filament::icon x-show="showPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="vault-new-password-confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirma la contraseña</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="vault-new-password-confirmation"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="setupForm.passwordConfirmation"
                                autocomplete="new-password"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <p
                        x-show="setupError"
                        x-text="setupError"
                        class="text-sm text-danger-600 dark:text-danger-400"
                    ></p>

                    <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <x-filament::input.checkbox x-model="setupForm.acknowledgedNoRecovery" class="mt-0.5" />
                        <span>Entiendo que si olvido esta contraseña perderé el acceso a todos los datos de mi bóveda.</span>
                    </label>

                    <x-filament::button
                        type="submit"
                        icon="heroicon-o-shield-check"
                        x-bind:disabled="!canCreateVault"
                    >
                        <span x-show="!unlocking">Crear bóveda</span>
                        <span x-show="unlocking">Preparando…</span>
                    </x-filament::button>
                </form>

                {{-- Unlock existing vault --}}
                <form
                    x-show="!isNewVault"
                    x-on:submit.prevent="unlock"
                    class="mt-6 flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña maestra</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="vault-password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                autocomplete="current-password"
                            />
                            <x-slot name="suffix">
                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                    x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                >
                                    <x-filament::icon x-show="!showPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                    <x-filament::icon x-show="showPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>
                    </div>

                    <p
                        x-show="unlockError"
                        x-text="unlockError"
                        class="text-sm text-danger-600 dark:text-danger-400"
                    ></p>

                    <x-filament::button
                        type="submit"
                        icon="heroicon-o-lock-open"
                        x-bind:disabled="unlocking || !password"
                    >
                        <span x-show="!unlocking">Desbloquear</span>
                        <span x-show="unlocking">Descifrando…</span>
                    </x-filament::button>

                    <div class="flex flex-col items-center gap-1 border-t border-gray-200 pt-4 text-center text-sm dark:border-white/10">
                        <span class="text-gray-500 dark:text-gray-400">¿Olvidaste tu contraseña?</span>
                        {{ $this->resetVaultAction }}
                    </div>
                </form>
            </x-filament::section>
        </div>

        {{-- Unlocked state --}}
        <div
            x-show="unlocked"
            class="flex flex-col gap-6 lg:flex-row"
        >
            {{-- Sidebar --}}
            <aside class="flex shrink-0 flex-col gap-6 lg:w-60">
                <nav class="flex flex-col gap-1">
                    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Bóveda</p>

                    <button
                        type="button"
                        x-on:click="filterFolderId = null; filterType = 'all'"
                        x-bind:class="filterFolderId === null && filterType === 'all' ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium"
                    >
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-5 w-5 shrink-0" />
                        <span class="flex-1 text-start">Todos los ítems</span>
                        <span class="text-xs tabular-nums text-gray-400" x-text="decryptedItems.length"></span>
                    </button>

                    <button
                        type="button"
                        x-on:click="filterFolderId = null; filterType = 'favorites'"
                        x-bind:class="filterType === 'favorites' ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium"
                    >
                        <x-filament::icon icon="heroicon-o-star" class="h-5 w-5 shrink-0" />
                        <span class="flex-1 text-start">Favoritos</span>
                        <span class="text-xs tabular-nums text-gray-400" x-text="favoritesCount"></span>
                    </button>
                </nav>

                <nav class="flex flex-col gap-1">
                    <div class="flex items-center justify-between px-3 pb-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Carpetas</p>
                        <x-filament::icon-button
                            icon="heroicon-m-plus"
                            size="sm"
                            color="gray"
                            label="Nueva carpeta"
                            x-on:click="openNewFolderForm"
                        />
                    </div>

                    <template x-for="folder in decryptedFolders" x-bind:key="folder.id">
                        <button
                            type="button"
                            x-on:click="filterFolderId = folder.id"
                            x-bind:class="filterFolderId === folder.id ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium"
                        >
                            <x-filament::icon icon="heroicon-o-folder" class="h-5 w-5 shrink-0" />
                            <span x-text="folderLabel(folder.id)" class="flex-1 truncate text-start" x-bind:class="folder.isCorrupted && 'italic text-danger-600 dark:text-danger-400'"></span>
                            <span class="text-xs tabular-nums text-gray-400" x-text="folderItemsCount(folder.id)"></span>
                        </button>
                    </template>

                    <p
                        x-show="decryptedFolders.length === 0"
                        class="px-3 text-sm text-gray-400 dark:text-gray-500"
                    >
                        Sin carpetas todavía.
                    </p>
                </nav>

                <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-lock-closed"
                        x-on:click="lock"
                    >
                        Bloquear bóveda
                    </x-filament::button>

                    <div class="rounded-lg border border-danger-200 p-3 dark:border-danger-500/30">
                        <p class="text-xs font-semibold uppercase tracking-wide text-danger-600 dark:text-danger-400">Zona de peligro</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Borra todo el contenido y te permite elegir una nueva contraseña maestra.
                        </p>
                        <div class="mt-2">
                            {{ $this->resetVaultAction }}
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Items --}}
            <div class="flex min-w-0 flex-1 flex-col gap-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" class="sm:max-w-sm sm:flex-1">
                        <x-filament::input
                            type="search"
                            x-model="search"
                            placeholder="Buscar por título, usuario, URL o etiqueta…"
                        />
                    </x-filament::input.wrapper>

                    <x-filament::dropdown placement="bottom-end">
                        <x-slot name="trigger">
                            <x-filament::button icon="heroicon-m-plus">
                                Nuevo ítem
                            </x-filament::button>
                        </x-slot>

                        <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item icon="heroicon-o-key" x-on:click="openNewItemForm('password'); close()">
                                Contraseña
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item icon="heroicon-o-document-text" x-on:click="openNewItemForm('note'); close()">
                                Nota segura
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item icon="heroicon-o-shield-check" x-on:click="openNewItemForm('recovery_code'); close()">
                                Código de recuperación
                            </x-filament::dropdown.list.item>
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                </div>

                {{-- Some blocks could not be decrypted --}}
                <div
                    x-show="corruptedItemsCount > 0 || corruptedFoldersCount > 0"
                    class="flex gap-3 rounded-lg bg-danger-50 p-3 text-sm ring-1 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/30"
                >
                    <x-filament::icon
                        icon="heroicon-m-exclamation-triangle"
                        class="mt-0.5 h-5 w-5 shrink-0 text-danger-600 dark:text-danger-400"
                    />
                    <div class="flex flex-col gap-1 text-danger-800 dark:text-danger-200">
                        <p class="font-semibold">Algunos datos no se pudieron descifrar</p>
                        <p>
                            Tu contraseña es correcta, pero
                            <span x-show="corruptedItemsCount > 0"><strong x-text="corruptedItemsCount"></strong> ítem(s)</span>
                            <span x-show="corruptedItemsCount > 0 && corruptedFoldersCount > 0">y</span>
                            <span x-show="corruptedFoldersCount > 0"><strong x-text="corruptedFoldersCount"></strong> carpeta(s)</span>
                            están dañados. Se muestran como «ilegibles»; el resto de tu bóveda funciona con normalidad.
                        </p>
                    </div>
                </div>

                {{-- Type filter --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'all' => 'Todos',
                        'password' => 'Contraseñas',
                        'note' => 'Notas',
                        'recovery_code' => 'Códigos',
                    ] as $type => $label)
                        <button
                            type="button"
                            x-on:click="filterType = @js($type)"
                            x-bind:class="filterType === @js($type) ? 'bg-primary-600 text-white ring-primary-600 dark:bg-primary-500 dark:ring-primary-500' : 'bg-white text-gray-700 ring-gray-950/10 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10'"
                            class="rounded-full px-3 py-1 text-xs font-medium ring-1"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Empty vault --}}
                <div
                    x-show="decryptedItems.length === 0"
                    class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center dark:border-gray-700"
                >
                    <x-filament::icon icon="heroicon-o-archive-box" class="h-8 w-8 text-gray-400" />
                    <p class="font-medium text-gray-950 dark:text-white">Tu bóveda está vacía</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Agrega tu primera contraseña, nota o código con «Nuevo ítem».</p>
                </div>

                {{-- No results for the current filters --}}
                <div
                    x-show="decryptedItems.length > 0 && visibleItems.length === 0"
                    class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center dark:border-gray-700"
                >
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-8 w-8 text-gray-400" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay ítems que coincidan con los filtros.</p>
                    <x-filament::link tag="button" x-on:click="clearFilters" size="sm">
                        Limpiar filtros
                    </x-filament::link>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="item in visibleItems" x-bind:key="item.id">
                        <div
                            class="flex flex-col rounded-xl p-4 shadow-sm ring-1"
                            x-bind:class="item.isCorrupted ? 'bg-danger-50 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/30' : 'bg-white ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10'"
                        >
                            {{-- Item that could not be decrypted --}}
                            <template x-if="item.isCorrupted">
                                <div class="flex h-full flex-col gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-danger-700 dark:text-danger-300">Ítem ilegible</p>
                                            <p class="text-sm text-danger-700/80 dark:text-danger-300/80">
                                                Este ítem está dañado y no se pudo descifrar. El resto de tu bóveda no se ve afectado.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-auto flex justify-end pt-1">
                                        <x-filament::icon-button
                                            icon="heroicon-o-trash"
                                            label="Eliminar"
                                            color="danger"
                                            x-on:click="confirmDeleteItem(item)"
                                        />
                                    </div>
                                </div>
                            </template>

                            <template x-if="!item.isCorrupted">
                                <div class="flex h-full flex-col gap-3">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                        x-bind:class="{
                                            'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400': item.type === 'password',
                                            'bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400': item.type === 'note',
                                            'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400': item.type === 'recovery_code',
                                        }"
                                    >
                                        <x-filament::icon x-show="item.type === 'password'" icon="heroicon-o-key" class="h-5 w-5" />
                                        <x-filament::icon x-show="item.type === 'note'" icon="heroicon-o-document-text" class="h-5 w-5" />
                                        <x-filament::icon x-show="item.type === 'recovery_code'" icon="heroicon-o-shield-check" class="h-5 w-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p x-text="item.data.title" class="truncate font-medium text-gray-950 dark:text-white"></p>
                                        <p
                                            x-show="folderLabel(item.folderId)"
                                            class="flex items-center gap-1 truncate text-xs text-gray-400 dark:text-gray-500"
                                        >
                                            <x-filament::icon icon="heroicon-m-folder" class="h-3.5 w-3.5 shrink-0" />
                                            <span x-text="folderLabel(item.folderId)" class="truncate"></span>
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="toggleFavorite(item)"
                                        class="shrink-0 text-gray-400 hover:text-warning-500"
                                        x-bind:aria-label="item.isFavorite ? 'Quitar de favoritos' : 'Marcar como favorito'"
                                    >
                                        <x-filament::icon x-show="item.isFavorite" icon="heroicon-s-star" class="h-5 w-5 text-warning-500" />
                                        <x-filament::icon x-show="!item.isFavorite" icon="heroicon-o-star" class="h-5 w-5" />
                                    </button>
                                </div>

                                {{-- Password details --}}
                                <template x-if="item.type === 'password'">
                                    <dl class="flex flex-col gap-1.5 text-sm">
                                        <div x-show="item.data.username" class="flex items-center gap-2">
                                            <dt class="sr-only">Usuario</dt>
                                            <x-filament::icon icon="heroicon-m-user" class="h-4 w-4 shrink-0 text-gray-400" />
                                            <dd x-text="item.data.username" class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300"></dd>
                                            <button
                                                type="button"
                                                x-on:click="copy(item.data.username, `username-${item.id}`)"
                                                class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                aria-label="Copiar usuario"
                                            >
                                                <x-filament::icon x-show="copiedKey !== `username-${item.id}`" icon="heroicon-m-clipboard-document" class="h-4 w-4" />
                                                <x-filament::icon x-show="copiedKey === `username-${item.id}`" icon="heroicon-m-check" class="h-4 w-4 text-success-500" />
                                            </button>
                                        </div>

                                        <div x-show="item.data.password" class="flex items-center gap-2">
                                            <dt class="sr-only">Contraseña</dt>
                                            <x-filament::icon icon="heroicon-m-key" class="h-4 w-4 shrink-0 text-gray-400" />
                                            <dd
                                                x-text="isRevealed(item) ? item.data.password : '••••••••••'"
                                                class="min-w-0 flex-1 truncate font-mono text-gray-600 dark:text-gray-300"
                                            ></dd>
                                            <button
                                                type="button"
                                                x-on:click="toggleReveal(item)"
                                                class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                x-bind:aria-label="isRevealed(item) ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                            >
                                                <x-filament::icon x-show="!isRevealed(item)" icon="heroicon-m-eye" class="h-4 w-4" />
                                                <x-filament::icon x-show="isRevealed(item)" icon="heroicon-m-eye-slash" class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="copy(item.data.password, `password-${item.id}`)"
                                                class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                aria-label="Copiar contraseña"
                                            >
                                                <x-filament::icon x-show="copiedKey !== `password-${item.id}`" icon="heroicon-m-clipboard-document" class="h-4 w-4" />
                                                <x-filament::icon x-show="copiedKey === `password-${item.id}`" icon="heroicon-m-check" class="h-4 w-4 text-success-500" />
                                            </button>
                                        </div>

                                        <div x-show="safeUrl(item.data.url)" class="flex items-center gap-2">
                                            <dt class="sr-only">URL</dt>
                                            <x-filament::icon icon="heroicon-m-globe-alt" class="h-4 w-4 shrink-0 text-gray-400" />
                                            <dd class="min-w-0 flex-1 truncate">
                                                <a
                                                    x-bind:href="safeUrl(item.data.url)"
                                                    x-text="item.data.url"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-primary-600 hover:underline dark:text-primary-400"
                                                ></a>
                                            </dd>
                                        </div>
                                    </dl>
                                </template>

                                {{-- Recovery code --}}
                                <template x-if="item.type === 'recovery_code'">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5">
                                        <span
                                            x-text="isRevealed(item) ? item.data.code : '••••••••••'"
                                            class="min-w-0 flex-1 truncate font-mono text-sm text-gray-700 dark:text-gray-200"
                                        ></span>
                                        <button
                                            type="button"
                                            x-on:click="toggleReveal(item)"
                                            class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            x-bind:aria-label="isRevealed(item) ? 'Ocultar código' : 'Mostrar código'"
                                        >
                                            <x-filament::icon x-show="!isRevealed(item)" icon="heroicon-m-eye" class="h-4 w-4" />
                                            <x-filament::icon x-show="isRevealed(item)" icon="heroicon-m-eye-slash" class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="copy(item.data.code, `code-${item.id}`)"
                                            class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            aria-label="Copiar código"
                                        >
                                            <x-filament::icon x-show="copiedKey !== `code-${item.id}`" icon="heroicon-m-clipboard-document" class="h-4 w-4" />
                                            <x-filament::icon x-show="copiedKey === `code-${item.id}`" icon="heroicon-m-check" class="h-4 w-4 text-success-500" />
                                        </button>
                                    </div>
                                </template>

                                {{-- Note --}}
                                <p
                                    x-show="item.data.note"
                                    x-text="item.data.note"
                                    class="line-clamp-3 whitespace-pre-line text-sm text-gray-500 dark:text-gray-400"
                                ></p>

                                <div class="mt-auto flex items-end justify-between gap-2 pt-1">
                                    <div class="flex min-w-0 flex-wrap gap-1">
                                        <template x-for="tag in (item.data.tags ?? [])" x-bind:key="tag">
                                            <x-filament::badge size="sm" color="gray">
                                                <span x-text="tag"></span>
                                            </x-filament::badge>
                                        </template>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-1">
                                        <x-filament::icon-button
                                            icon="heroicon-o-pencil-square"
                                            color="gray"
                                            label="Editar"
                                            x-on:click="openEditItemForm(item)"
                                        />
                                        <x-filament::icon-button
                                            icon="heroicon-o-trash"
                                            label="Eliminar"
                                            color="danger"
                                            x-on:click="confirmDeleteItem(item)"
                                        />
                                    </div>
                                </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Item form modal --}}
        <x-filament::modal id="vault-item-form-modal" width="lg">
            <x-slot name="heading">
                <span x-text="itemForm.id ? 'Editar ítem' : {
                    password: 'Nueva contraseña',
                    note: 'Nueva nota segura',
                    recovery_code: 'Nuevo código de recuperación',
                }[itemForm.type]"></span>
            </x-slot>

            <form
                x-on:submit.prevent="submitItemForm"
                class="flex flex-col gap-4"
            >
                <div class="flex flex-col gap-1.5">
                    <label for="vault-item-title" class="text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="vault-item-title"
                            type="text"
                            x-model="itemForm.title"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>

                <template x-if="itemForm.type === 'password'">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="vault-item-username" class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuario</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="vault-item-username" type="text" x-model="itemForm.username" autocomplete="off" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="vault-item-password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                                <x-filament::link tag="button" type="button" size="sm" icon="heroicon-m-sparkles" x-on:click="generateItemPassword">
                                    Generar
                                </x-filament::link>
                            </div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="vault-item-password"
                                    x-bind:type="showItemFormPassword ? 'text' : 'password'"
                                    x-model="itemForm.password"
                                    autocomplete="new-password"
                                    class="font-mono"
                                />
                                <x-slot name="suffix">
                                    <button
                                        type="button"
                                        x-on:click="showItemFormPassword = !showItemFormPassword"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                        x-bind:aria-label="showItemFormPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    >
                                        <x-filament::icon x-show="!showItemFormPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                        <x-filament::icon x-show="showItemFormPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                    </button>
                                </x-slot>
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="vault-item-url" class="text-sm font-medium text-gray-700 dark:text-gray-300">URL</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="vault-item-url" type="text" x-model="itemForm.url" placeholder="https://" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </template>

                <template x-if="itemForm.type === 'recovery_code'">
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-item-code" class="text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
                        <x-filament::input.wrapper>
                            <x-filament::input id="vault-item-code" type="text" x-model="itemForm.code" class="font-mono" />
                        </x-filament::input.wrapper>
                    </div>
                </template>

                <div class="flex flex-col gap-1.5">
                    <label for="vault-item-note" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                    <x-filament::input.wrapper>
                        <textarea
                            id="vault-item-note"
                            x-model="itemForm.note"
                            rows="3"
                            class="fi-input block w-full border-none bg-transparent px-3 py-1.5 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500"
                        ></textarea>
                    </x-filament::input.wrapper>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-item-folder" class="text-sm font-medium text-gray-700 dark:text-gray-300">Carpeta</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select id="vault-item-folder" x-model="itemForm.folderId">
                                <option value="">Sin carpeta</option>
                                <template x-for="folder in decryptedFolders" x-bind:key="folder.id">
                                    <option x-bind:value="folder.id" x-text="folderLabel(folder.id)"></option>
                                </template>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="vault-item-tags" class="text-sm font-medium text-gray-700 dark:text-gray-300">Etiquetas</label>
                        <x-filament::input.wrapper>
                            <x-filament::input id="vault-item-tags" type="text" x-model="itemForm.tags" placeholder="trabajo, banco" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <x-filament::input.checkbox x-model="itemForm.isFavorite" />
                    Marcar como favorito
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <x-filament::button
                        color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'vault-item-form-modal' })"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit" icon="heroicon-m-lock-closed">
                        Cifrar y guardar
                    </x-filament::button>
                </div>
            </form>
        </x-filament::modal>

        {{-- Folder form modal --}}
        <x-filament::modal id="vault-folder-form-modal" width="md">
            <x-slot name="heading">
                Nueva carpeta
            </x-slot>

            <form
                x-on:submit.prevent="submitFolderForm"
                class="flex flex-col gap-4"
            >
                <div class="flex flex-col gap-1.5">
                    <label for="vault-folder-name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="vault-folder-name"
                            type="text"
                            x-model="folderName"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex justify-end gap-3">
                    <x-filament::button
                        color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'vault-folder-form-modal' })"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit">
                        Crear carpeta
                    </x-filament::button>
                </div>
            </form>
        </x-filament::modal>

        {{-- Delete item confirmation modal --}}
        <x-filament::modal
            id="vault-delete-item-modal"
            width="md"
            icon="heroicon-o-trash"
            icon-color="danger"
        >
            <x-slot name="heading">
                Eliminar ítem
            </x-slot>

            <x-slot name="description">
                <span>¿Eliminar <strong x-text="pendingDeleteItem?.isCorrupted ? 'este ítem ilegible' : pendingDeleteItem?.data.title"></strong> de tu bóveda?</span>
            </x-slot>

            <x-slot name="footerActions">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'vault-delete-item-modal' })"
                >
                    Cancelar
                </x-filament::button>
                <x-filament::button color="danger" x-on:click="deleteItem">
                    Eliminar
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>
</x-filament-panels::page>
