@php
    // Buscador nativo (input + <datalist>) en vez de un <select> normal: con
    // muchas categorías/contextos, escribir para filtrar es más rápido que
    // scrollear una lista larga. Se prefirió <datalist> sobre un combobox de
    // Alpine hecho a mano porque el navegador ya resuelve abrir/cerrar,
    // filtrado y navegación por teclado — menos JS propio que pueda fallar.
    $listId = $wireModel.'-options';
    $currentLabel = $current !== null && $current !== '' ? ($options[$current] ?? null) : null;
@endphp

<div
    x-data="{
        query: @js($currentLabel ?? ''),
        options: @js($options),
        init() {
            // Resincroniza el texto mostrado si `$wireModel` cambia por otra
            // vía (URL/back-forward, u otro control) — el valor inicial de
            // `query` solo se evalúa una vez al montar el nodo.
            this.$watch('$wire.{{ $wireModel }}', (value) => {
                this.query = value ? (this.options[value] ?? '') : '';
            });
        },
    }"
    class="w-full"
>
    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
        <x-filament::input
            type="text"
            list="{{ $listId }}"
            x-model="query"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            x-on:input="
                const match = Array.from($el.list.options).find((option) => option.value === $event.target.value);
                if (match) {
                    $wire.set('{{ $wireModel }}', Number(match.dataset.id));
                } else if ($event.target.value === '') {
                    $wire.set('{{ $wireModel }}', null);
                }
            "
        />
    </x-filament::input.wrapper>

    <datalist id="{{ $listId }}">
        @foreach ($options as $id => $name)
            <option data-id="{{ $id }}" value="{{ $name }}"></option>
        @endforeach
    </datalist>
</div>
