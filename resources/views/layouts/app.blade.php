<!doctype html>
<html lang="vi" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Gốc kho ảnh do disk đang dùng quyết định
        window.STORAGE_BASE = @json(rtrim(Storage::url(''), '/'));

        window.storageUrl = function(path) {
            if (!path) return '';
            if (/^https?:\/\//i.test(path)) return path;
            return window.STORAGE_BASE + '/' + String(path).replace(/^\/+/, '');
        };
    </script>

    <title>{{ config('app.name', 'Kho Hàng Phương Lê') }} - Quản Trị</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- jQuery nạp trước bundle Vite --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 antialiased font-sans flex flex-col min-h-screen">

    {{-- Topbar Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 h-16 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-700/80">
        <div class="px-3 sm:px-4 h-full flex items-center justify-between gap-2">
            
            {{-- Left: Mobile hamburger & Brand --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button id="toggleSidebarMobile" type="button" aria-label="Mở menu" aria-controls="sidebar" aria-expanded="false"
                    class="p-2 text-slate-600 rounded-lg lg:hidden hover:bg-slate-100 active:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700 dark:active:bg-slate-600 focus:outline-none cursor-pointer select-none">
                    <svg id="toggleSidebarMobileHamburger" class="w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="toggleSidebarMobileClose" class="hidden w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-base font-bold tracking-tight text-slate-900 dark:text-white">PHƯƠNG LÊ</span>
                        <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800">Admin</span>
                    </div>
                </a>
            </div>

            {{-- Center: Quick search / POS shortcut --}}
            <div class="hidden md:flex items-center flex-1 max-w-md mx-4">
                <a href="{{ route('order') }}" class="w-full flex items-center justify-between px-3.5 py-1.5 text-xs text-slate-500 bg-slate-100 hover:bg-slate-200/70 dark:bg-slate-700/50 dark:hover:bg-slate-700 dark:text-slate-400 rounded-lg border border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Tìm kiếm hàng hóa, đơn hàng hoặc khách...</span>
                    </span>
                    <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 bg-white dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded shadow-xs">F2</kbd>
                </a>
            </div>

            {{-- Right: Actions & User menu --}}
            <div class="flex items-center gap-1.5 sm:gap-2">
                {{-- Quick POS sale button --}}
                <a href="{{ route('order') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-lg shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Bán hàng (POS)</span>
                    <span class="sm:hidden">Bán</span>
                </a>

                {{-- Dark Mode Toggle --}}
                <button id="theme-toggle" type="button" aria-label="Đổi giao diện Sáng / Tối"
                    class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <svg id="theme-toggle-dark-icon" class="hidden w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707 .707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                    </svg>
                </button>

                {{-- User Profile Dropdown --}}
                <div class="relative flex items-center ml-1">
                    <button type="button" id="user-menu-button-2" data-dropdown-toggle="dropdown-2"
                        class="flex items-center gap-2 p-1 text-sm rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                        <img class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                            src="{{ empty(Auth::user()->avatar) ? asset('images/no-photo.svg') : Storage::url(Auth::user()->avatar) }}"
                            alt="{{ Auth::user()->name }}">
                        <span class="hidden xl:inline text-xs font-semibold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</span>
                    </button>

                    <div id="dropdown-2" class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-slate-100 rounded-xl shadow-xl border border-slate-200/80 dark:bg-slate-800 dark:divide-slate-700 dark:border-slate-700 w-56">
                        <div class="px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                        <ul class="py-1 text-sm text-slate-700 dark:text-slate-300">
                            <li>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-white">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Hồ sơ cá nhân</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('settings.sales') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-white">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Cài đặt hệ thống</span>
                                </a>
                            </li>
                        </ul>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-left">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Layout Container --}}
    <div class="flex pt-16 overflow-hidden flex-1">

        {{-- Sidebar Navigation --}}
        <aside id="sidebar" aria-label="Sidebar"
            class="fixed top-0 left-0 z-40 flex flex-col flex-shrink-0 w-64 h-full pt-16 duration-200 ease-in-out transition-transform -translate-x-full lg:translate-x-0 bg-white border-r border-slate-200/80 dark:bg-slate-800 dark:border-slate-700/80">
            <div class="relative flex flex-col flex-1 min-h-0 bg-white border-r border-slate-200/80 dark:bg-slate-800 dark:border-slate-700/80">
                <div class="flex flex-col flex-1 pt-3 pb-4 overflow-y-auto custom-scrollbar px-3 space-y-4">
                    
                    {{-- Nhóm: BÁN HÀNG & ĐƠN --}}
                    <div>
                        <div class="px-2 mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Bán hàng & Đơn
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                    </svg>
                                    <span>Tổng quan</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('order') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('order') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('order') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>Quản lý đơn hàng</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Nhóm: HÀNG HÓA & KHO --}}
                    <div>
                        <div class="px-2 mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Hàng hóa & Kho
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('product') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('product*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('product*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span>Sản phẩm</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventory') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('inventory*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('inventory*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <span>Tồn kho & Kiểm kê</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('categories') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('categories*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('categories*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span>Danh mục hàng</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('supplier') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supplier*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('supplier*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Nhà cung cấp</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Nhóm: KHÁCH HÀNG --}}
                    <div>
                        <div class="px-2 mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Khách hàng
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('customer') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('customer*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('customer*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Khách hàng & VIP</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Nhóm: HỆ THỐNG & NỘI DUNG --}}
                    <div>
                        <div class="px-2 mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Hệ thống
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('content') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('content*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('content*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Web bán hàng</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('settings.sales') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('settings*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('settings*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    <span>Cài đặt bán hàng</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('account') }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('account*') ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white font-medium' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('account*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Nhân viên & Phân quyền</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </aside>

        {{-- Backdrop for Mobile --}}
        <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-slate-900/60 backdrop-blur-xs lg:hidden transition-opacity"></div>

        {{-- Main Page Content --}}
        <div id="main-content" class="relative flex flex-col flex-1 w-full h-full min-h-[calc(100vh-4rem)] overflow-y-auto bg-slate-50 lg:ml-64 dark:bg-slate-900">
            <main class="flex-1 p-3 sm:p-4 md:p-6 pb-12">
                {{ $slot }}
            </main>
            @include('layouts.footer')
        </div>

    </div>

    {{-- Dark Mode & Mobile Sidebar Script --}}
    <script>
        (function() {
            try {
                const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
                const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
                const themeToggleBtn = document.getElementById('theme-toggle');

                if (themeToggleDarkIcon && themeToggleLightIcon) {
                    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        themeToggleLightIcon.classList.remove('hidden');
                    } else {
                        themeToggleDarkIcon.classList.remove('hidden');
                    }
                }

                if (themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', function() {
                        if (themeToggleDarkIcon) themeToggleDarkIcon.classList.toggle('hidden');
                        if (themeToggleLightIcon) themeToggleLightIcon.classList.toggle('hidden');

                        if (localStorage.getItem('color-theme')) {
                            if (localStorage.getItem('color-theme') === 'light') {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('color-theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('color-theme', 'light');
                            }
                        } else {
                            if (document.documentElement.classList.contains('dark')) {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('color-theme', 'light');
                            } else {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('color-theme', 'dark');
                            }
                        }
                    });
                }
            } catch (err) {
                console.warn('Theme toggle error:', err);
            }

            // Fallback Mobile Sidebar Toggle
            try {
                const sidebar = document.getElementById('sidebar');
                const toggleSidebarBtn = document.getElementById('toggleSidebarMobile');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');
                const hamburgerIcon = document.getElementById('toggleSidebarMobileHamburger');
                const closeIcon = document.getElementById('toggleSidebarMobileClose');

                function toggleMobile() {
                    if (window.toggleMobileSidebar) {
                        window.toggleMobileSidebar();
                        return;
                    }
                    if (!sidebar) return;
                    const isOpen = sidebar.classList.contains('translate-x-0') || !sidebar.classList.contains('-translate-x-full');
                    if (!isOpen) {
                        sidebar.classList.remove('-translate-x-full');
                        sidebar.classList.add('translate-x-0');
                        if (sidebarBackdrop) sidebarBackdrop.classList.remove('hidden');
                        if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
                        if (closeIcon) closeIcon.classList.remove('hidden');
                        if (toggleSidebarBtn) toggleSidebarBtn.setAttribute('aria-expanded', 'true');
                    } else {
                        sidebar.classList.add('-translate-x-full');
                        sidebar.classList.remove('translate-x-0');
                        if (sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
                        if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
                        if (closeIcon) closeIcon.classList.add('hidden');
                        if (toggleSidebarBtn) toggleSidebarBtn.setAttribute('aria-expanded', 'false');
                    }
                }

                if (toggleSidebarBtn) {
                    toggleSidebarBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleMobile();
                    });
                }
                if (sidebarBackdrop) {
                    sidebarBackdrop.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (window.toggleMobileSidebar) window.toggleMobileSidebar(false);
                        else toggleMobile();
                    });
                }
            } catch (err) {
                console.warn('Sidebar toggle error:', err);
            }
        })();
    </script>
</body>

</html>

