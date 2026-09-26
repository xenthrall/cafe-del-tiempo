@props([
    'href',
    'variant' => 'primary',
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-medium text-sm transition-colors',
        'bg-amber-600 hover:bg-amber-500 text-white shadow-sm shadow-amber-900/10' => $variant === 'primary',
        'bg-stone-900 hover:bg-stone-800 text-white dark:bg-stone-800 dark:hover:bg-stone-700' => $variant === 'dark',
        'border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:border-stone-400 hover:bg-white/60 dark:hover:border-stone-600 dark:hover:bg-stone-900/60' => $variant === 'outline',
    ]) }}
>
    {{ $slot }}
</a>
