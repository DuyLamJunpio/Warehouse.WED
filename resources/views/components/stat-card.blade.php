@props([
    'title' => '',
    'label' => '',
    'value' => '0',
    'subtitle' => null,
    'trend' => null,     // integer e.g. 15, -10
    'trendLabel' => 'so với hôm qua',
    'icon' => null,
    'color' => 'indigo', // indigo, emerald, amber, rose, purple, neutral
    'href' => null,
])

@php
    $cardTitle = $title !== '' ? $title : ($label !== '' ? $label : '');
    $tag = $href ? 'a' : 'div';
    $hoverClass = $href ? 'hover:border-indigo-300 dark:hover:border-indigo-800 hover:shadow-md cursor-pointer transition-all duration-150' : '';

    $colorStyles = [
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400',
        'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400',
        'neutral' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
    ];
    $iconBg = $colorStyles[$color] ?? $colorStyles['indigo'];
@endphp

<{{ $tag }} {{ $href ? 'href='.$href : '' }} {{ $attributes->merge(['class' => 'block p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs ' . $hoverClass]) }}>
    <div class="flex items-center justify-between gap-2 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="{{ $cardTitle }}">{{ $cardTitle }}</span>
        @if ($icon)
            <div class="p-2 rounded-xl {{ $iconBg }} shrink-0">
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
    @elseif (!$slot->isEmpty())
        <div class="text-xs text-slate-500 dark:text-slate-400">
            {{ $slot }}
        </div>
    @endif
</{{ $tag }}>

