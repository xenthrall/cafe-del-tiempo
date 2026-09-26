<x-filament-panels::page>
    @vite(['resources/js/vault.js'])

    @php
        $collectionButtonClass = 'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition';
        $collectionActiveClass = 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400';
        $collectionIdleClass = 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5';

        $chipClass = 'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium ring-1 transition';
        $chipActiveClass = 'bg-primary-600 text-white ring-primary-600 dark:bg-primary-500 dark:ring-primary-500';
        $chipIdleClass = 'bg-white text-gray-700 ring-gray-950/10 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10';

        // Mobile rows that scroll sideways and bleed to the screen edge.
        $scrollRowClass = '-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:-mx-6 sm:px-6 [&::-webkit-scrollbar]:hidden';

        // ≥ 36px tap target on mobile, a bit tighter with a mouse.
        $inlineActionClass = 'inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200 sm:size-8';

        $fieldLabelClass = 'text-sm font-medium text-gray-700 dark:text-gray-300';

        $itemTypes = [
            'password' => ['label' => 'Contraseña', 'plural' => 'Contraseñas', 'icon' => 'heroicon-o-key'],
            'note' => ['label' => 'Nota segura', 'plural' => 'Notas', 'icon' => 'heroicon-o-document-text'],
            'recovery_code' => ['label' => 'Código de recuperación', 'plural' => 'Códigos', 'icon' => 'heroicon-o-shield-check'],
        ];
    @endphp

    <div
        x-data="vaultApp()"
        x-on:vault-reset.window="lock()"
        x-cloak
        class="flex flex-col gap-6"
    >
        {{-- Page header (replaces Filament's default heading, see HidesPageHeader) --}}
        <header class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2 text-gray-500 dark:text-gray-400">
                <x-filament::icon x-show="!unlocked" icon="heroicon-o-lock-closed" class="h-5 w-5 shrink-0" />
                <x-filament::icon x-show="unlocked" icon="heroicon-o-lock-open" class="h-5 w-5 shrink-0" />
                <h1 class="text-lg font-semibold text-gray-950 dark:text-white">Bóveda</h1>
                <span x-show="unlocked" class="hidden sm:inline-flex">
                    <x-filament::badge color="success" icon="heroicon-m-shield-check">
                        Desbloqueada
                    </x-filament::badge>
                </span>
            </div>

            <div x-show="unlocked" class="flex shrink-0 items-center gap-2">
                <div class="hidden sm:block">
                    <x-filament::button color="gray" icon="heroicon-m-lock-closed" x-on:click="lock">
                        Bloquear
                    </x-filament::button>
                </div>

                <div class="sm:hidden">
                    <x-filament::icon-button
                        icon="heroicon-o-lock-closed"
                        color="gray"
                        size="lg"
                        label="Bloquear bóveda"
                        x-on:click="lock"
                    />
                </div>

                <div class="hidden lg:block">
                    <x-filament::dropdown placement="bottom-end">
                        <x-slot name="trigger">
                            <x-filament::button icon="heroicon-m-plus">
                                Nuevo ítem
                            </x-filament::button>
                        </x-slot>

                        <x-filament::dropdown.list>
                            @foreach ($itemTypes as $type => $meta)
                                <x-filament::dropdown.list.item :icon="$meta['icon']" x-on:click="openNewItemForm({{ Js::from($type) }}); close()">
                                    {{ $meta['label'] }}
                                </x-filament::dropdown.list.item>
                            @endforeach
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                </div>

                <x-filament::dropdown placement="bottom-end">
                    <x-slot name="trigger">
                        <x-filament::icon-button
                            icon="heroicon-m-ellipsis-vertical"
                            color="gray"
                            size="lg"
                            label="Más opciones"
                        />
                    </x-slot>

                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item icon="heroicon-o-folder-plus" x-on:click="openNewFolderForm(); close()">
                            Nueva carpeta
                        </x-filament::dropdown.list.item>
                    </x-filament::dropdown.list>

                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item
                            icon="heroicon-o-arrow-path"
                            color="danger"
                            x-on:click="close(); $wire.mountAction('resetVault')"
                        >
                            Reiniciar bóveda
                        </x-filament::dropdown.list.item>
                    </x-filament::dropdown.list>
                </x-filament::dropdown>
            </div>
        </header>

        {{-- Locked state --}}
        <div
            x-show="!unlocked"
            class="mx-auto w-full max-w-md sm:py-6"
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
                            ? 'Tu bóveda se protege con una clave maestra propia, distinta de la contraseña de tu cuenta.'
                            : 'Ingresa la clave maestra de tu bóveda. Se usa solo en este navegador y nunca se envía al servidor.'"
                    ></p>
                </div>

                {{-- No-recovery warning --}}
                <div class="mt-6 flex gap-3 rounded-lg bg-warning-50 p-3 text-sm ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:ring-warning-400/30">
                    <x-filament::icon
                        icon="heroicon-m-exclamation-triangle"
                        class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400"
                    />
                    <div class="flex flex-col gap-1 text-warning-800 dark:text-warning-200">
                        <p class="font-semibold">Tu clave maestra no se puede recuperar</p>
                        <p>
                            Nadie, ni siquiera nosotros, puede ver ni restablecer tu clave maestra.
                            Si la olvidas o la pierdes, <strong>no será posible recuperar los datos de tu bóveda</strong>;
                            solo podrás reiniciarla y empezar de cero.
                        </p>
                    </div>
                </div>

                {{--
                    Autofill is blocked on purpose on every master key field: otherwise the
                    browser offers the saved account (login) password here, which is exactly
                    the mix-up these screens try to prevent.
                --}}

                {{-- Create vault (first time) --}}
                <form
                    x-show="isNewVault"
                    x-on:submit.prevent="createVault"
                    autocomplete="off"
                    class="mt-6 flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-new-password" class="{{ $fieldLabelClass }}">Crea tu clave maestra</label>
                        <x-filament::input.wrapper prefix-icon="heroicon-m-shield-check">
                            <x-filament::input
                                id="vault-new-password"
                                name="vault-master-key"
                                x-ref="newPasswordInput"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="setupForm.password"
                                autocomplete="off"
                                data-1p-ignore
                                data-lpignore="true"
                                data-bwignore
                                data-form-type="other"
                            />
                            <x-slot name="suffix">
                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="{{ $inlineActionClass }} -me-2"
                                    x-bind:aria-label="showPassword ? 'Ocultar clave' : 'Mostrar clave'"
                                >
                                    <x-filament::icon x-show="!showPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                    <x-filament::icon x-show="showPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Usa una clave distinta a la contraseña con la que inicias sesión.
                        </p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="vault-new-password-confirmation" class="{{ $fieldLabelClass }}">Confirma la clave maestra</label>
                        <x-filament::input.wrapper prefix-icon="heroicon-m-shield-check">
                            <x-filament::input
                                id="vault-new-password-confirmation"
                                name="vault-master-key-confirmation"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="setupForm.passwordConfirmation"
                                autocomplete="off"
                                data-1p-ignore
                                data-lpignore="true"
                                data-bwignore
                                data-form-type="other"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <p
                        x-show="setupError"
                        x-text="setupError"
                        class="text-sm text-danger-600 dark:text-danger-400"
                    ></p>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg p-2 -m-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-filament::input.checkbox x-model="setupForm.acknowledgedNoRecovery" class="mt-0.5" />
                        <span>Entiendo que si olvido esta clave maestra perderé el acceso a todos los datos de mi bóveda.</span>
                    </label>

                    <x-filament::button
                        type="submit"
                        size="lg"
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
                    autocomplete="off"
                    class="mt-6 flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-password" class="{{ $fieldLabelClass }}">Clave maestra</label>
                        <x-filament::input.wrapper prefix-icon="heroicon-m-shield-check">
                            <x-filament::input
                                id="vault-password"
                                name="vault-master-key"
                                x-ref="passwordInput"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                autocomplete="off"
                                data-1p-ignore
                                data-lpignore="true"
                                data-bwignore
                                data-form-type="other"
                            />
                            <x-slot name="suffix">
                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="{{ $inlineActionClass }} -me-2"
                                    x-bind:aria-label="showPassword ? 'Ocultar clave' : 'Mostrar clave'"
                                >
                                    <x-filament::icon x-show="!showPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                    <x-filament::icon x-show="showPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Es la clave que creaste para tu bóveda, no la contraseña de tu cuenta.
                        </p>
                    </div>

                    <p
                        x-show="unlockError"
                        x-text="unlockError"
                        class="text-sm text-danger-600 dark:text-danger-400"
                    ></p>

                    <x-filament::button
                        type="submit"
                        size="lg"
                        icon="heroicon-o-lock-open"
                        x-bind:disabled="unlocking || !password"
                    >
                        <span x-show="!unlocking">Desbloquear</span>
                        <span x-show="unlocking">Descifrando…</span>
                    </x-filament::button>

                    <div class="flex flex-col items-center gap-1 border-t border-gray-200 pt-4 text-center text-sm dark:border-white/10">
                        <span class="text-gray-500 dark:text-gray-400">¿Olvidaste tu clave maestra?</span>
                        {{ $this->resetVaultAction }}
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            Reiniciar la bóveda no afecta la contraseña de tu cuenta.
                            @if (filament()->hasProfile())
                                Esa se cambia desde
                                <x-filament::link :href="filament()->getProfileUrl()" size="xs">tu perfil</x-filament::link>.
                            @endif
                        </p>
                    </div>
                </form>
            </x-filament::section>
        </div>

        {{-- Unlocked state --}}
        <div
            x-show="unlocked"
            class="flex flex-col gap-4 pb-24 lg:flex-row lg:gap-8 lg:pb-0"
        >
            {{-- Desktop sidebar --}}
            <aside class="hidden lg:sticky lg:top-24 lg:flex lg:w-60 lg:shrink-0 lg:flex-col lg:gap-6 lg:self-start">
                <nav class="flex flex-col gap-1" aria-label="Colecciones">
                    <button
                        type="button"
                        x-on:click="selectCollection('all')"
                        x-bind:class="isCollectionActive('all') ? @js($collectionActiveClass) : @js($collectionIdleClass)"
                        class="{{ $collectionButtonClass }}"
                    >
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-5 w-5 shrink-0" />
                        <span class="flex-1 text-start">Todos los ítems</span>
                        <span class="text-xs tabular-nums text-gray-400" x-text="decryptedItems.length"></span>
                    </button>

                    <button
                        type="button"
                        x-on:click="selectCollection('favorites')"
                        x-bind:class="isCollectionActive('favorites') ? @js($collectionActiveClass) : @js($collectionIdleClass)"
                        class="{{ $collectionButtonClass }}"
                    >
                        <x-filament::icon icon="heroicon-o-star" class="h-5 w-5 shrink-0" />
                        <span class="flex-1 text-start">Favoritos</span>
                        <span class="text-xs tabular-nums text-gray-400" x-text="favoritesCount"></span>
                    </button>
                </nav>

                <nav class="flex flex-col gap-1" aria-label="Carpetas">
                    <div class="flex items-center justify-between ps-3">
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
                            x-on:click="selectCollection(folder.id)"
                            x-bind:class="isCollectionActive(folder.id) ? @js($collectionActiveClass) : @js($collectionIdleClass)"
                            class="{{ $collectionButtonClass }}"
                        >
                            <x-filament::icon icon="heroicon-o-folder" class="h-5 w-5 shrink-0" />
                            <span
                                x-text="folderLabel(folder.id)"
                                x-bind:class="folder.isCorrupted && 'italic text-danger-600 dark:text-danger-400'"
                                class="flex-1 truncate text-start"
                            ></span>
                            <span class="text-xs tabular-nums text-gray-400" x-text="folderItemsCount(folder.id)"></span>
                        </button>
                    </template>

                    <button
                        type="button"
                        x-show="decryptedFolders.length === 0"
                        x-on:click="openNewFolderForm"
                        class="{{ $collectionButtonClass }} {{ $collectionIdleClass }} text-gray-500 dark:text-gray-400"
                    >
                        <x-filament::icon icon="heroicon-o-folder-plus" class="h-5 w-5 shrink-0" />
                        Crear tu primera carpeta
                    </button>
                </nav>

                <p class="flex items-center gap-1.5 px-3 text-xs text-gray-400 dark:text-gray-500">
                    <x-filament::icon icon="heroicon-m-shield-check" class="h-4 w-4 shrink-0" />
                    Cifrado de extremo a extremo en este navegador.
                </p>
            </aside>

            {{-- Content --}}
            <div class="@container flex min-w-0 flex-1 flex-col gap-4">
                {{-- Mobile collections --}}
                <nav class="{{ $scrollRowClass }} lg:hidden" aria-label="Colecciones">
                    <button
                        type="button"
                        x-on:click="selectCollection('all')"
                        x-bind:class="isCollectionActive('all') ? @js($chipActiveClass) : @js($chipIdleClass)"
                        class="{{ $chipClass }}"
                    >
                        Todos
                        <span class="text-xs tabular-nums opacity-70" x-text="decryptedItems.length"></span>
                    </button>

                    <button
                        type="button"
                        x-on:click="selectCollection('favorites')"
                        x-bind:class="isCollectionActive('favorites') ? @js($chipActiveClass) : @js($chipIdleClass)"
                        class="{{ $chipClass }}"
                    >
                        <x-filament::icon icon="heroicon-m-star" class="h-4 w-4" />
                        Favoritos
                    </button>

                    <template x-for="folder in decryptedFolders" x-bind:key="folder.id">
                        <button
                            type="button"
                            x-on:click="selectCollection(folder.id)"
                            x-bind:class="isCollectionActive(folder.id) ? @js($chipActiveClass) : @js($chipIdleClass)"
                            class="{{ $chipClass }}"
                        >
                            <x-filament::icon icon="heroicon-m-folder" class="h-4 w-4" />
                            <span x-text="folderLabel(folder.id)" class="max-w-40 truncate"></span>
                        </button>
                    </template>

                    <button
                        type="button"
                        x-on:click="openNewFolderForm"
                        class="{{ $chipClass }} {{ $chipIdleClass }} border-dashed text-gray-500 dark:text-gray-400"
                    >
                        <x-filament::icon icon="heroicon-m-folder-plus" class="h-4 w-4" />
                        Carpeta
                    </button>
                </nav>

                {{-- Desktop collection title --}}
                <div class="hidden items-baseline gap-2 lg:flex">
                    <h2 x-text="activeCollectionLabel" class="truncate text-base font-semibold text-gray-950 dark:text-white"></h2>
                    <span class="text-sm tabular-nums text-gray-400" x-text="`${visibleItems.length} ${visibleItems.length === 1 ? 'ítem' : 'ítems'}`"></span>
                </div>

                {{-- Search + type filter --}}
                <div class="flex flex-col gap-3 @3xl:flex-row @3xl:items-center">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" class="@3xl:max-w-sm @3xl:flex-1">
                        <x-filament::input
                            type="search"
                            x-model="search"
                            placeholder="Buscar por título, usuario, URL o etiqueta…"
                            autocapitalize="none"
                            spellcheck="false"
                        />
                    </x-filament::input.wrapper>

                    <div class="{{ $scrollRowClass }} lg:mx-0 lg:px-0 @3xl:ms-auto">
                        <button
                            type="button"
                            x-on:click="filterType = 'all'"
                            x-bind:class="filterType === 'all' ? @js($chipActiveClass) : @js($chipIdleClass)"
                            class="{{ $chipClass }}"
                        >
                            Todos los tipos
                        </button>

                        @foreach ($itemTypes as $type => $meta)
                            <button
                                type="button"
                                x-on:click="filterType = {{ Js::from($type) }}"
                                x-bind:class="filterType === {{ Js::from($type) }} ? @js($chipActiveClass) : @js($chipIdleClass)"
                                class="{{ $chipClass }}"
                            >
                                <x-filament::icon :icon="$meta['icon']" class="h-4 w-4" />
                                {{ $meta['plural'] }}
                            </button>
                        @endforeach
                    </div>
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
                            Tu clave maestra es correcta, pero
                            <span x-show="corruptedItemsCount > 0"><strong x-text="corruptedItemsCount"></strong> ítem(s)</span>
                            <span x-show="corruptedItemsCount > 0 && corruptedFoldersCount > 0">y</span>
                            <span x-show="corruptedFoldersCount > 0"><strong x-text="corruptedFoldersCount"></strong> carpeta(s)</span>
                            están dañados. Se muestran como «ilegibles»; el resto de tu bóveda funciona con normalidad.
                        </p>
                    </div>
                </div>

                {{-- Empty vault --}}
                <div
                    x-show="decryptedItems.length === 0"
                    class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center dark:border-gray-700"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                        <x-filament::icon icon="heroicon-o-archive-box" class="h-6 w-6 text-gray-400" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="font-medium text-gray-950 dark:text-white">Tu bóveda está vacía</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Guarda aquí tus contraseñas, notas y códigos de recuperación.</p>
                    </div>
                    <x-filament::button icon="heroicon-m-plus" x-on:click="openNewItemForm('password')">
                        Agregar mi primera contraseña
                    </x-filament::button>
                </div>

                {{-- No results for the current filters --}}
                <div
                    x-show="decryptedItems.length > 0 && visibleItems.length === 0"
                    class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center dark:border-gray-700"
                >
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-8 w-8 text-gray-400" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay ítems que coincidan con los filtros.</p>
                    <x-filament::button color="gray" size="sm" x-on:click="clearFilters">
                        Limpiar filtros
                    </x-filament::button>
                </div>

                {{-- Items --}}
                <div class="grid grid-cols-1 gap-3 @xl:grid-cols-2 @5xl:grid-cols-3">
                    <template x-for="item in visibleItems" x-bind:key="item.id">
                        <div
                            class="flex flex-col rounded-xl p-4 shadow-sm ring-1"
                            x-bind:class="item.isCorrupted ? 'bg-danger-50 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/30' : 'bg-white ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10'"
                        >
                            {{-- Item that could not be decrypted --}}
                            <template x-if="item.isCorrupted">
                                <div class="flex h-full flex-col gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
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
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                                            x-bind:class="{
                                                'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400': item.type === 'password',
                                                'bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400': item.type === 'note',
                                                'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400': item.type === 'recovery_code',
                                            }"
                                        >
                                            @foreach ($itemTypes as $type => $meta)
                                                <x-filament::icon x-show="item.type === {{ Js::from($type) }}" :icon="$meta['icon']" class="h-5 w-5" />
                                            @endforeach
                                        </div>

                                        <button
                                            type="button"
                                            x-on:click="openEditItemForm(item)"
                                            class="min-w-0 flex-1 text-start"
                                        >
                                            <p x-text="item.data.title" class="truncate font-medium text-gray-950 hover:underline dark:text-white"></p>
                                            <p
                                                x-show="folderLabel(item.folderId)"
                                                class="flex items-center gap-1 truncate text-xs text-gray-400 dark:text-gray-500"
                                            >
                                                <x-filament::icon icon="heroicon-m-folder" class="h-3.5 w-3.5 shrink-0" />
                                                <span x-text="folderLabel(item.folderId)" class="truncate"></span>
                                            </p>
                                        </button>

                                        <button
                                            type="button"
                                            x-on:click="toggleFavorite(item)"
                                            class="{{ $inlineActionClass }} -me-2 -mt-1 hover:text-warning-500"
                                            x-bind:aria-label="item.isFavorite ? 'Quitar de favoritos' : 'Marcar como favorito'"
                                        >
                                            <x-filament::icon x-show="item.isFavorite" icon="heroicon-s-star" class="h-5 w-5 text-warning-500" />
                                            <x-filament::icon x-show="!item.isFavorite" icon="heroicon-o-star" class="h-5 w-5" />
                                        </button>
                                    </div>

                                    {{-- Password details --}}
                                    <template x-if="item.type === 'password'">
                                        <dl class="flex flex-col divide-y divide-gray-100 rounded-lg bg-gray-50 text-sm dark:divide-white/5 dark:bg-white/5">
                                            <div x-show="item.data.username" class="flex items-center gap-2 py-0.5 ps-3 pe-1">
                                                <dt class="sr-only">Usuario</dt>
                                                <x-filament::icon icon="heroicon-m-user" class="h-4 w-4 shrink-0 text-gray-400" />
                                                <dd x-text="item.data.username" class="min-w-0 flex-1 truncate text-gray-700 dark:text-gray-200"></dd>
                                                <button
                                                    type="button"
                                                    x-on:click="copy(item.data.username, `username-${item.id}`)"
                                                    class="{{ $inlineActionClass }}"
                                                    aria-label="Copiar usuario"
                                                >
                                                    <x-filament::icon x-show="copiedKey !== `username-${item.id}`" icon="heroicon-m-clipboard-document" class="h-4 w-4" />
                                                    <x-filament::icon x-show="copiedKey === `username-${item.id}`" icon="heroicon-m-check" class="h-4 w-4 text-success-500" />
                                                </button>
                                            </div>

                                            <div x-show="item.data.password" class="flex items-center gap-2 py-0.5 ps-3 pe-1">
                                                <dt class="sr-only">Contraseña</dt>
                                                <x-filament::icon icon="heroicon-m-key" class="h-4 w-4 shrink-0 text-gray-400" />
                                                <dd
                                                    x-text="isRevealed(item) ? item.data.password : '••••••••••'"
                                                    class="min-w-0 flex-1 truncate font-mono text-gray-700 dark:text-gray-200"
                                                ></dd>
                                                <button
                                                    type="button"
                                                    x-on:click="toggleReveal(item)"
                                                    class="{{ $inlineActionClass }}"
                                                    x-bind:aria-label="isRevealed(item) ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                                >
                                                    <x-filament::icon x-show="!isRevealed(item)" icon="heroicon-m-eye" class="h-4 w-4" />
                                                    <x-filament::icon x-show="isRevealed(item)" icon="heroicon-m-eye-slash" class="h-4 w-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="copy(item.data.password, `password-${item.id}`)"
                                                    class="{{ $inlineActionClass }}"
                                                    aria-label="Copiar contraseña"
                                                >
                                                    <x-filament::icon x-show="copiedKey !== `password-${item.id}`" icon="heroicon-m-clipboard-document" class="h-4 w-4" />
                                                    <x-filament::icon x-show="copiedKey === `password-${item.id}`" icon="heroicon-m-check" class="h-4 w-4 text-success-500" />
                                                </button>
                                            </div>

                                            <div x-show="safeUrl(item.data.url)" class="flex items-center gap-2 py-0.5 ps-3 pe-1">
                                                <dt class="sr-only">URL</dt>
                                                <x-filament::icon icon="heroicon-m-globe-alt" class="h-4 w-4 shrink-0 text-gray-400" />
                                                <dd x-text="item.data.url" class="min-w-0 flex-1 truncate text-gray-700 dark:text-gray-200"></dd>
                                                <a
                                                    x-bind:href="safeUrl(item.data.url)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="{{ $inlineActionClass }}"
                                                    aria-label="Abrir sitio"
                                                >
                                                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-4 w-4" />
                                                </a>
                                            </div>
                                        </dl>
                                    </template>

                                    {{-- Recovery code --}}
                                    <template x-if="item.type === 'recovery_code'">
                                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 py-0.5 ps-3 pe-1 dark:bg-white/5">
                                            <span
                                                x-text="isRevealed(item) ? item.data.code : '••••••••••'"
                                                class="min-w-0 flex-1 truncate font-mono text-sm text-gray-700 dark:text-gray-200"
                                            ></span>
                                            <button
                                                type="button"
                                                x-on:click="toggleReveal(item)"
                                                class="{{ $inlineActionClass }}"
                                                x-bind:aria-label="isRevealed(item) ? 'Ocultar código' : 'Mostrar código'"
                                            >
                                                <x-filament::icon x-show="!isRevealed(item)" icon="heroicon-m-eye" class="h-4 w-4" />
                                                <x-filament::icon x-show="isRevealed(item)" icon="heroicon-m-eye-slash" class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="copy(item.data.code, `code-${item.id}`)"
                                                class="{{ $inlineActionClass }}"
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

                                        <div class="-me-2 -mb-2 flex shrink-0 items-center">
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

        {{-- Mobile floating "new item" button --}}
        <div
            x-show="unlocked"
            class="fixed inset-e-4 bottom-[calc(1rem+env(safe-area-inset-bottom))] z-20 lg:hidden"
        >
            <x-filament::dropdown placement="top-end">
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="flex size-14 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 active:scale-95 dark:bg-primary-500 dark:hover:bg-primary-400"
                        aria-label="Nuevo ítem"
                    >
                        <x-filament::icon icon="heroicon-m-plus" class="h-7 w-7" />
                    </button>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach ($itemTypes as $type => $meta)
                        <x-filament::dropdown.list.item :icon="$meta['icon']" x-on:click="openNewItemForm({{ Js::from($type) }}); close()">
                            {{ $meta['label'] }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
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
                    <label for="vault-item-title" class="{{ $fieldLabelClass }}">Título</label>
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
                            <label for="vault-item-username" class="{{ $fieldLabelClass }}">Usuario</label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="vault-item-username"
                                    type="text"
                                    x-model="itemForm.username"
                                    autocomplete="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="vault-item-password" class="{{ $fieldLabelClass }}">Contraseña</label>
                                <x-filament::link tag="button" type="button" size="sm" icon="heroicon-m-sparkles" x-on:click="generateItemPassword">
                                    Generar
                                </x-filament::link>
                            </div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="vault-item-password"
                                    x-bind:type="showItemFormPassword ? 'text' : 'password'"
                                    x-model="itemForm.password"
                                    autocomplete="off"
                                    data-1p-ignore
                                    data-lpignore="true"
                                    data-bwignore
                                    data-form-type="other"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    class="font-mono"
                                />
                                <x-slot name="suffix">
                                    <button
                                        type="button"
                                        x-on:click="showItemFormPassword = !showItemFormPassword"
                                        class="{{ $inlineActionClass }} -me-2"
                                        x-bind:aria-label="showItemFormPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    >
                                        <x-filament::icon x-show="!showItemFormPassword" icon="heroicon-m-eye" class="h-5 w-5" />
                                        <x-filament::icon x-show="showItemFormPassword" icon="heroicon-m-eye-slash" class="h-5 w-5" />
                                    </button>
                                </x-slot>
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="vault-item-url" class="{{ $fieldLabelClass }}">URL</label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="vault-item-url"
                                    type="text"
                                    inputmode="url"
                                    x-model="itemForm.url"
                                    placeholder="https://"
                                    autocapitalize="none"
                                    spellcheck="false"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </template>

                <template x-if="itemForm.type === 'recovery_code'">
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-item-code" class="{{ $fieldLabelClass }}">Código</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="vault-item-code"
                                type="text"
                                x-model="itemForm.code"
                                autocapitalize="none"
                                spellcheck="false"
                                class="font-mono"
                            />
                        </x-filament::input.wrapper>
                    </div>
                </template>

                <div class="flex flex-col gap-1.5">
                    <label for="vault-item-note" class="{{ $fieldLabelClass }}">Nota</label>
                    <x-filament::input.wrapper>
                        <textarea
                            id="vault-item-note"
                            x-model="itemForm.note"
                            rows="3"
                            class="fi-input block w-full resize-y border-none bg-transparent px-3 py-1.5 text-base text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 sm:text-sm dark:text-white dark:placeholder:text-gray-500"
                        ></textarea>
                    </x-filament::input.wrapper>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label for="vault-item-folder" class="{{ $fieldLabelClass }}">Carpeta</label>
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
                        <label for="vault-item-tags" class="{{ $fieldLabelClass }}">Etiquetas</label>
                        <x-filament::input.wrapper>
                            <x-filament::input id="vault-item-tags" type="text" x-model="itemForm.tags" placeholder="trabajo, banco" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <x-filament::input.checkbox x-model="itemForm.isFavorite" />
                    Marcar como favorito
                </label>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                    <x-filament::button
                        color="gray"
                        class="w-full sm:w-auto"
                        x-on:click="$dispatch('close-modal', { id: 'vault-item-form-modal' })"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit" icon="heroicon-m-lock-closed" class="w-full sm:w-auto">
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
                    <label for="vault-folder-name" class="{{ $fieldLabelClass }}">Nombre</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="vault-folder-name"
                            type="text"
                            x-model="folderName"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-filament::button
                        color="gray"
                        class="w-full sm:w-auto"
                        x-on:click="$dispatch('close-modal', { id: 'vault-folder-form-modal' })"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit" class="w-full sm:w-auto">
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
