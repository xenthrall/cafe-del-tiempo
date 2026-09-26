@props([
    'variant' => 'info',
    'title' => null,
])

<aside {{ $attributes->class([
    'flex flex-col gap-1 px-4 py-3 rounded-xl border text-sm leading-relaxed',
    'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-200' => $variant === 'info',
    'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200' => $variant === 'warning',
    'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-200' => $variant === 'tip',
]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</aside>
