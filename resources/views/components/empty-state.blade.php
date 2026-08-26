@props([
    'title' => 'Không có dữ liệu',
    'description' => 'Hiện chưa có mục nào trong danh sách này.',
    'icon' => 'box',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 sm:p-12 text-center']) }}>
    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mb-3 shadow-inner">
        @if ($icon === 'search')
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        @elseif ($icon === 'order')
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        @elseif ($icon === 'user')
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        @else
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        @endif
    </div>
    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-1">{{ $title }}</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-4">{{ $description }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-1">
            {{ $slot }}
        </div>
    @endif
</div>
