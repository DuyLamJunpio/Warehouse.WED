<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RUNGU') }} — Quản trị cửa hàng</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="/" class="inline-flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-primary-700 flex items-center justify-center text-white shadow-md shadow-primary-700/20 font-display font-bold text-xl tracking-[0.08em]">
                RU
            </div>
        </a>
        <h2 class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            Hệ thống Quản trị Bán hàng
        </h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            RUNGU • Quản lý cửa hàng &amp; bán lẻ
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-slate-800 py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
