@props([
    'eyebrow',
    'title',
])

<div {{ $attributes->class('text-center max-w-2xl mx-auto') }}>
    <p class="text-xs font-medium uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3">{{ $eyebrow }}</p>
    <h2 class="text-3xl font-semibold text-stone-900 dark:text-stone-100 tracking-tight">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="text-sm sm:text-base text-stone-600 dark:text-stone-400 mt-3">{{ $slot }}</p>
    @endif
</div>
