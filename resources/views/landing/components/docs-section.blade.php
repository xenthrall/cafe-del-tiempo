@props([
    'id',
    'title',
])

<section id="{{ $id }}" {{ $attributes->class('flex flex-col gap-5 scroll-mt-24') }}>
    <h2 class="group text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100">
        {{ $title }}
        <a href="#{{ $id }}" class="ml-1 text-stone-300 dark:text-stone-600 opacity-0 group-hover:opacity-100 transition-opacity" aria-label="Enlace a esta sección">#</a>
    </h2>
    <div class="flex flex-col gap-4 text-[15px] leading-relaxed text-stone-700 dark:text-stone-300 [&_a]:font-medium [&_a]:text-amber-700 dark:[&_a]:text-amber-400 [&_a:hover]:underline [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-stone-900 dark:[&_h3]:text-stone-100 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:flex [&_ol]:flex-col [&_ol]:gap-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:flex [&_ul]:flex-col [&_ul]:gap-1.5 [&_:not(pre)>code]:px-1.5 [&_:not(pre)>code]:py-0.5 [&_:not(pre)>code]:rounded-md [&_:not(pre)>code]:bg-stone-200/60 dark:[&_:not(pre)>code]:bg-stone-800 [&_:not(pre)>code]:text-[13px] [&_:not(pre)>code]:font-mono [&_table]:w-full [&_table]:text-sm [&_th]:text-left [&_th]:font-medium [&_th]:text-stone-900 dark:[&_th]:text-stone-100 [&_th]:py-2.5 [&_th]:pr-4 [&_td]:py-2.5 [&_td]:pr-4 [&_td]:align-top [&_tr]:border-b [&_tr]:border-stone-200 dark:[&_tr]:border-stone-800">
        {{ $slot }}
    </div>
</section>
