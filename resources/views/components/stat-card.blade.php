@props([
    'title' => '',
    'value' => '0',
    'subtitle' => null,
    'trend' => null,     // integer e.g. 15, -10
    'trendLabel' => 'so với hôm qua',
    'icon' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
    $hoverClass = $href ? 'hover:border-indigo-300 dark:hover:border-indigo-800 hover:shadow-md cursor-pointer transition-all duration-150' : '';
@endphp

<{{ $tag }} {{ $href ? 'href='.$href : '' }} {{ $attributes->merge(['class' => 'block p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm ' . $hoverClass]) }}>
    <div class="flex items-center justify-between gap-2 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $title }}</span>
        @if ($icon)
            <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1.5">
        {{ $value }}
    </div>

    @if ($trend !== null)
        <div class="flex items-center gap-1.5 text-xs font-medium">
            @if ($trend > 0)
                <span class="text-emerald-600 dark:text-emerald-400 flex items-center font-semibold">
                    <svg class="w-3.5 h-3.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +{{ $trend }}%
                </span>
            @elseif ($trend < 0)
                <span class="text-rose-600 dark:text-rose-400 flex items-center font-semibold">
                    <svg class="w-3.5 h-3.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    {{ $trend }}%
                </span>
            @else
                <span class="text-slate-500 font-semibold">0%</span>
            @endif
            <span class="text-slate-500 dark:text-slate-400 font-normal">{{ $trendLabel }}</span>
        </div>
    @elseif ($subtitle)
        <div class="text-xs text-slate-500 dark:text-slate-400">
            {{ $subtitle }}
        </div>
    @endif
</{{ $tag }}>
