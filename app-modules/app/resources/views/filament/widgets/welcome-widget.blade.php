<x-filament-widgets::widget>
    <div class="mx-auto max-w-4xl py-8">

        <div class="flex flex-col items-center gap-3 text-center">
            <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-300/60 bg-amber-50/80 px-3 py-1 text-xs font-medium text-amber-800 dark:border-amber-700/50 dark:bg-amber-950/40 dark:text-amber-300">
                <x-filament::icon icon="heroicon-m-sparkles" class="h-3.5 w-3.5" />
                Café IA
            </div>

            <h2 class="text-4xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $this->getGreeting() }}, {{ filament()->auth()->user()->name }}.
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pregúntame sobre tus finanzas, tu bóveda o cualquier cosa de tu suite.
            </p>
        </div>

        <div x-data="{ prompt: '' }" class="mt-8 flex flex-col gap-3">
            <div
                class="
                    overflow-hidden
                    rounded-3xl
                    border
                    border-gray-200
                    bg-white
                    shadow-sm
                    transition-colors
                    focus-within:border-amber-300
                    dark:border-white/10
                    dark:bg-gray-900
                    dark:focus-within:border-amber-700
                ">

                <textarea
                    x-ref="promptInput"
                    x-model="prompt"
                    rows="4"
                    placeholder="Escribe tu pregunta..."
                    class="
                        w-full
                        resize-none
                        border-0
                        bg-transparent
                        px-6
                        pt-6
                        text-base
                        text-gray-900
                        placeholder:text-gray-400

                        outline-none
                        focus:outline-none
                        focus:border-transparent
                        focus:ring-0
                        focus:ring-transparent
                        focus-visible:outline-none

                        dark:text-white
                        dark:placeholder:text-gray-500
                    "></textarea>

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                        border-t
                        border-gray-100
                        px-5
                        py-4
                        dark:border-white/10
                    ">

                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4 text-amber-500" />
                        Café IA
                    </span>

                    <button
                        type="button"
                        x-bind:disabled="prompt.trim() === ''"
                        class="
                            flex
                            h-10
                            w-10
                            items-center
                            justify-center
                            rounded-full
                            bg-amber-600
                            text-white
                            transition
                            hover:bg-amber-500
                            disabled:cursor-not-allowed
                            disabled:bg-gray-200
                            disabled:text-gray-400
                            dark:disabled:bg-white/10
                            dark:disabled:text-gray-600
                        ">
                        <x-filament::icon icon="heroicon-m-arrow-up" class="h-5 w-5" />
                    </button>

                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-2">
                @foreach ($this->getSuggestions() as $suggestion)
                    <button
                        type="button"
                        x-on:click="prompt = @js($suggestion); $refs.promptInput.focus()"
                        class="
                            rounded-full
                            border
                            border-gray-200
                            px-3.5
                            py-1.5
                            text-xs
                            font-medium
                            text-gray-600
                            transition-colors
                            hover:border-amber-300
                            hover:text-amber-700
                            dark:border-white/10
                            dark:text-gray-400
                            dark:hover:border-amber-700
                            dark:hover:text-amber-400
                        ">
                        {{ $suggestion }}
                    </button>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
