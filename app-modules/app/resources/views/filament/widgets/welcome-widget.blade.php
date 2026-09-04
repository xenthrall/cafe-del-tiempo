<x-filament-widgets::widget>
    <div class="mx-auto max-w-4xl py-8">

        <div class="space-y-2 text-center">

            <h2 class="text-4xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $this->getGreeting() }}, {{ filament()->auth()->user()->name }}.
            </h2>

            <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <x-heroicon-o-arrow-path class="h-4 w-4" />

                <span>Recent chat</span>

                <span>·</span>

                <span class="truncate">
                    hola mundo
                </span>
            </div>

        </div>

        <div
            class="
                mt-8
                overflow-hidden
                rounded-3xl
                border
                border-gray-200
                bg-white
                shadow-sm
                dark:border-white/10
                dark:bg-gray-900
            ">

            <textarea rows="5" placeholder="Ask anything..."
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
                    justify-end
                    gap-4
                    border-t
                    border-gray-100
                    px-5
                    py-4
                    dark:border-white/10
                ">

                <button
                    class="
                        text-sm
                        font-medium
                        text-gray-600
                        hover:text-gray-900
                        dark:text-gray-300
                        dark:hover:text-white
                    ">
                    Auto
                </button>

                <button
                    class="
                        flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-full
                        bg-primary-600
                        text-white
                        transition
                        hover:bg-primary-500
                    ">
                    <x-heroicon-m-arrow-up class="h-5 w-5" />
                </button>

            </div>

        </div>

    </div>
</x-filament-widgets::widget>
