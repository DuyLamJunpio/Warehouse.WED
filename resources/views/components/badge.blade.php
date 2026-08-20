@props([
    'variant' => 'neutral', // success, warning, danger, info, purple, neutral
    'size' => 'sm',        // xs, sm, md
])

@php
    $variants = [
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/50',
        'purple' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/50',
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
    ];

    $sizes = [
        'xs' => 'px-2 py-0.5 text-[11px] font-medium leading-4',
        'sm' => 'px-2.5 py-0.5 text-xs font-medium leading-4',
        'md' => 'px-3 py-1 text-sm font-medium leading-5',
    ];

    $classes = 'inline-flex items-center gap-1.5 rounded-full border ' . ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['sm']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
