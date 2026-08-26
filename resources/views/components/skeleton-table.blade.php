@props([
    'rows' => 5,
    'cols' => 6,
])

<div class="w-full divide-y divide-slate-200 dark:divide-slate-700 animate-pulse">
    @for ($i = 0; $i < $rows; $i++)
        <div class="flex items-center gap-4 py-3.5 px-4">
            <div class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/3"></div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded w-1/5"></div>
            </div>
            <div class="hidden sm:block w-24 h-4 bg-slate-200 dark:bg-slate-700 rounded"></div>
            <div class="hidden md:block w-20 h-4 bg-slate-200 dark:bg-slate-700 rounded"></div>
            <div class="w-16 h-6 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
            <div class="w-20 h-8 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
        </div>
    @endfor
</div>
