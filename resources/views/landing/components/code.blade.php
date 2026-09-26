@props([
    'title' => 'bash',
])

<div data-code-block {{ $attributes->class('rounded-2xl border border-stone-200 dark:border-stone-800 bg-[#161412] shadow-sm overflow-hidden') }}>
    <div class="flex items-center justify-between px-4 py-2 border-b border-stone-800">
        <span class="text-[11px] font-mono text-stone-500">{{ $title }}</span>
        <button type="button" data-copy class="text-[11px] font-medium text-stone-400 hover:text-stone-100 transition-colors">
            Copiar
        </button>
    </div>
    <pre class="p-5 font-mono text-xs leading-relaxed text-stone-100 overflow-x-auto"><code>{{ $slot }}</code></pre>
</div>
