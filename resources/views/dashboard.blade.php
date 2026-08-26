<x-app-layout>
    @php
        $tien = fn($n) => number_format($n, 0, ',', '.') . ' ₫';

        $badgeVariant = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'packing' => 'purple',
            'shipping' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'returned' => 'neutral',
        ];
    @endphp

    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Tổng quan kinh doanh
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Cập nhật thời gian thực về doanh thu, đơn hàng và tồn kho sản phẩm.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('order') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Bán hàng (POS)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 4 KPI Stat Cards ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Doanh thu hôm nay" :value="$tien($kpi['revenue_today'])" color="emerald" :trend="$kpi['revenue_change']">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
            <span class="text-xs text-slate-400">so với hôm qua</span>
        </x-stat-card>

        <x-stat-card label="Doanh thu tháng này" :value="$tien($kpi['revenue_month'])" color="indigo">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>
            <span class="text-xs text-slate-400">{{ $kpi['orders_month'] }} đơn hoàn thành</span>
        </x-stat-card>

        <a href="{{ route('order') }}" class="block group">
            <x-stat-card label="Đơn cần xử lý" :value="$kpi['orders_open']" color="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                    {{ $kpi['orders_pending'] > 0 ? $kpi['orders_pending'] . ' đơn chờ xác nhận' : 'Không có đơn chờ' }}
                </span>
            </x-stat-card>
        </a>

        <a href="{{ route('inventory') }}" class="block group">
            <x-stat-card label="Giá trị tồn kho" :value="$tien($kpi['stock_value'])" color="neutral">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </x-slot:icon>
                <span class="text-xs text-slate-400">{{ number_format($kpi['stock_units'], 0, ',', '.') }} sản phẩm</span>
            </x-stat-card>
        </a>
    </div>

    {{-- ── Biểu đồ doanh thu 14 ngày gần nhất ───────────────────────────── --}}
    <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Doanh thu 14 ngày gần nhất</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Chỉ tính đơn đã hoàn tất. Tổng 14 ngày:
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $tien($chart['total']) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="inline-block w-3 h-3 rounded-sm bg-indigo-500"></span> Doanh thu (VNĐ)
            </div>
        </div>

        @if ($chart['has_data'])
            @php
                $rongCot = 100 / count($chart['days']);
            @endphp
            <div class="overflow-x-auto custom-scrollbar pt-2">
                <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="w-full h-44" role="img">
                    <defs>
                        <linearGradient id="barGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.9"/>
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0.4"/>
                        </linearGradient>
                    </defs>
                    @foreach ($chart['days'] as $i => $d)
                        @php
                            $cao = $chart['max'] > 0 ? ($d['value'] / $chart['max']) * 28 : 0;
                        @endphp
                        <rect x="{{ $i * $rongCot + $rongCot * 0.15 }}" y="{{ 31 - max($cao, 0.4) }}"
                            width="{{ $rongCot * 0.7 }}" height="{{ max($cao, 0.4) }}" rx="0.5"
                            fill="url(#barGradient)" class="hover:opacity-80 transition-opacity">
                            <title>{{ $d['label'] }}: {{ $tien($d['value']) }}</title>
                        </rect>
                    @endforeach
                </svg>
                <div class="flex mt-2">
                    @foreach ($chart['days'] as $d)
                        <div class="text-[10px] text-center font-medium text-slate-400 dark:text-slate-500"
                            style="width: {{ $rongCot }}%">{{ $d['label'] }}</div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="py-12 text-center">
                <x-empty-state icon="orders" title="Chưa có dữ liệu doanh thu" description="Doanh thu sẽ được cập nhật tự động khi các đơn hàng hoàn tất." />
            </div>
        @endif
    </div>

    {{-- ── 2-Column Section: Top Selling & Low Stock ──────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Bán chạy nhất 30 ngày --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bán chạy nhất (30 ngày)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Xếp hạng theo số lượng đã bán</p>
                </div>
                <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse ($topProducts as $index => $p)
                    <div class="flex items-center justify-between py-3 gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $index == 0 ? 'bg-amber-100 text-amber-800' : ($index == 1 ? 'bg-slate-100 text-slate-700' : ($index == 2 ? 'bg-orange-100 text-orange-800' : 'bg-slate-50 text-slate-500')) }}">
                                {{ $index + 1 }}
                            </span>
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                                {{ $p['name'] }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $p['sold'] }} cái</div>
                            <div class="text-[11px] text-slate-400">{{ $tien($p['revenue']) }}</div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-xs text-center text-slate-400">Chưa có dữ liệu bán hàng 30 ngày qua.</p>
                @endforelse
            </div>
        </div>

        {{-- Cảnh báo tồn kho cần nhập --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cảnh báo tồn kho</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $kpi['out_of_stock'] }} hết hàng · {{ $kpi['low_stock'] }} sắp hết</p>
                </div>
                <a href="{{ route('inventory') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Xem tất cả ➔
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse ($lowStock as $v)
                    <div class="flex items-center justify-between py-3 gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $v['product'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $v['label'] }}</div>
                        </div>
                        <x-badge :variant="$v['quantity'] <= 0 ? 'danger' : 'warning'" size="xs">
                            {{ $v['quantity'] <= 0 ? 'Hết hàng' : 'Còn ' . $v['quantity'] }}
                        </x-badge>
                    </div>
                @empty
                    <p class="py-8 text-xs text-center text-slate-400">Kho hàng đang ở trạng thái an toàn.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ── Recent Orders Card ─────────────────────────────────────── --}}
    <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Đơn hàng mới nhất</h3>
                <p class="text-xs text-slate-400 mt-0.5">Các đơn hàng vừa được tạo gần đây</p>
            </div>
            <a href="{{ route('order') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                Quản lý đơn ➔
            </a>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse ($recentOrders as $o)
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $o['code'] }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $o['customer'] }} · {{ $o['time'] }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <x-badge :variant="$badgeVariant[$o['status']] ?? 'neutral'" size="xs">
                            {{ $o['status_label'] }}
                        </x-badge>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $tien($o['total']) }}</span>
                    </div>
                </div>
            @empty
                <p class="py-8 text-xs text-center text-slate-400">Chưa có đơn hàng nào.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
