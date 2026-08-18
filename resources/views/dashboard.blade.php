<x-app-layout>
    @php
        $tien = fn($n) => number_format($n, 0, ',', '.') . ' ₫';

        $mauTrangThai = [
            'pending' => 'text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-300',
            'confirmed' => 'text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-300',
            'packing' => 'text-indigo-800 bg-indigo-100 dark:bg-indigo-900 dark:text-indigo-300',
            'shipping' => 'text-yellow-800 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300',
            'completed' => 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-300',
            'cancelled' => 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-300',
            'returned' => 'text-orange-800 bg-orange-100 dark:bg-orange-900 dark:text-orange-300',
        ];
    @endphp

    <div class="px-4 pt-6">

        {{-- ── Bốn ô số liệu chính ─────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="text-sm font-normal text-gray-500 dark:text-gray-400">Doanh thu hôm nay</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tien($kpi['revenue_today']) }}</div>
                <div class="mt-1 text-xs">
                    @if ($kpi['revenue_change'] === null)
                        <span class="text-gray-500 dark:text-gray-400">Hôm qua chưa có doanh thu để so sánh</span>
                    @elseif ($kpi['revenue_change'] >= 0)
                        <span class="font-medium text-green-600">▲ {{ $kpi['revenue_change'] }}%</span>
                        <span class="text-gray-500 dark:text-gray-400">so với hôm qua</span>
                    @else
                        <span class="font-medium text-red-600">▼ {{ abs($kpi['revenue_change']) }}%</span>
                        <span class="text-gray-500 dark:text-gray-400">so với hôm qua</span>
                    @endif
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="text-sm font-normal text-gray-500 dark:text-gray-400">Doanh thu tháng này</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tien($kpi['revenue_month']) }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Tháng trước: {{ $tien($kpi['revenue_last_month']) }} · {{ $kpi['orders_month'] }} đơn
                </div>
            </div>

            <a href="{{ route('order') }}"
                class="block p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-sm font-normal text-gray-500 dark:text-gray-400">Đơn cần xử lý</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $kpi['orders_open'] }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($kpi['orders_pending'] > 0)
                        <span class="font-medium text-yellow-600">{{ $kpi['orders_pending'] }} đơn chờ xác nhận</span>
                    @else
                        Không có đơn nào chờ xác nhận
                    @endif
                </div>
            </a>

            <a href="{{ route('inventory') }}"
                class="block p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-sm font-normal text-gray-500 dark:text-gray-400">Giá trị tồn kho</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tien($kpi['stock_value']) }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($kpi['stock_units'], 0, ',', '.') }} sản phẩm · theo giá nhập
                </div>
            </a>
        </div>

        {{-- ── Biểu đồ doanh thu 14 ngày ───────────────────────────── --}}
        <div class="p-4 mt-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Doanh thu 14 ngày gần nhất</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Chỉ tính đơn đã hoàn thành. Tổng:
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $tien($chart['total']) }}</span>
                </p>
            </div>

            @if ($chart['has_data'])
                @php
                    // Vẽ thẳng bằng SVG: không cần thư viện biểu đồ, không phải build lại,
                    // và chạy được cả khi máy không có mạng.
                    $rongCot = 100 / count($chart['days']);
                @endphp
                <div class="overflow-x-auto">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="w-full h-48" role="img"
                        aria-label="Biểu đồ doanh thu 14 ngày gần nhất">
                        @foreach ($chart['days'] as $i => $d)
                            @php
                                $cao = $chart['max'] > 0 ? ($d['value'] / $chart['max']) * 30 : 0;
                            @endphp
                            {{-- Cột 0 vẫn vẽ một vạch mỏng để thấy ngày đó có tồn tại --}}
                            <rect x="{{ $i * $rongCot + $rongCot * 0.15 }}" y="{{ 31 - max($cao, 0.3) }}"
                                width="{{ $rongCot * 0.7 }}" height="{{ max($cao, 0.3) }}" rx="0.3"
                                class="fill-primary-600 dark:fill-primary-500">
                                <title>{{ $d['label'] }}: {{ $tien($d['value']) }}</title>
                            </rect>
                        @endforeach
                    </svg>
                    <div class="flex mt-1">
                        @foreach ($chart['days'] as $d)
                            <div class="text-[10px] text-center text-gray-400 dark:text-gray-500"
                                style="width: {{ $rongCot }}%">{{ $d['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="py-12 text-sm text-center text-gray-500 dark:text-gray-400">
                    Chưa có đơn nào hoàn thành trong 14 ngày qua.<br>
                    <span class="text-xs">Đơn chỉ tính vào doanh thu sau khi chuyển sang trạng thái "Hoàn
                        thành".</span>
                </div>
            @endif
        </div>

        {{-- ── Đơn theo trạng thái ─────────────────────────────────── --}}
        <div class="p-4 mt-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Đơn hàng theo trạng thái</h3>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ($statusCounts as $s)
                    <a href="{{ route('order') }}"
                        class="p-3 text-center rounded-lg bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600">
                        <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $s['count'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s['label'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">

            {{-- ── Bán chạy nhất ───────────────────────────────────── --}}
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Bán chạy 30 ngày qua</h3>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Tính theo số lượng đã bán của đơn hoàn thành
                </p>

                @forelse ($topProducts as $p)
                    <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $p['name'] }}</div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $p['sold'] }} cái</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tien($p['revenue']) }}</div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                        Chưa có đơn hoàn thành nào trong 30 ngày qua.
                    </p>
                @endforelse
            </div>

            {{-- ── Cảnh báo tồn kho ────────────────────────────────── --}}
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cần nhập thêm</h3>
                    <a href="{{ route('inventory') }}"
                        class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-500">Xem tất
                        cả</a>
                </div>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ $kpi['out_of_stock'] }} biến thể đã hết · {{ $kpi['low_stock'] }} biến thể sắp hết
                </p>

                @forelse ($lowStock as $v)
                    <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $v['product'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $v['label'] }}</div>
                        </div>
                        <span
                            class="px-2 py-1 text-xs font-medium rounded {{ $v['quantity'] <= 0 ? 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-300' : 'text-yellow-800 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300' }}">
                            {{ $v['quantity'] <= 0 ? 'Hết hàng' : 'Còn ' . $v['quantity'] }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                        Không có biến thể nào sắp hết hàng.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ── Đơn mới nhất ────────────────────────────────────────── --}}
        <div class="p-4 mt-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Đơn mới nhất</h3>
                <a href="{{ route('order') }}"
                    class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-500">Xem tất cả</a>
            </div>

            @forelse ($recentOrders as $o)
                <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $o['code'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $o['customer'] }} ·
                            {{ $o['time'] }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="px-2 py-1 text-xs font-medium rounded {{ $mauTrangThai[$o['status']] ?? 'text-gray-800 bg-gray-100' }}">
                            {{ $o['status_label'] }}
                        </span>
                        <span
                            class="text-sm font-semibold text-gray-900 dark:text-white">{{ $tien($o['total']) }}</span>
                    </div>
                </div>
            @empty
                <p class="py-8 text-sm text-center text-gray-500 dark:text-gray-400">Chưa có đơn hàng nào.</p>
            @endforelse
        </div>

        {{-- ── Số liệu phụ ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 mt-4 mb-6 lg:grid-cols-4">
            <a href="{{ route('product') }}"
                class="p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $kpi['products'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Sản phẩm</div>
            </a>
            <a href="{{ route('customer') }}"
                class="p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $kpi['customers'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Khách hàng · +{{ $kpi['customers_month'] }}
                    tháng này</div>
            </a>
            <a href="{{ route('inventory') }}"
                class="p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-xl font-bold text-yellow-500">{{ $kpi['low_stock'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Biến thể sắp hết</div>
            </a>
            <a href="{{ route('inventory') }}"
                class="p-4 bg-white rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="text-xl font-bold text-red-600">{{ $kpi['out_of_stock'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Biến thể hết hàng</div>
            </a>
        </div>
    </div>
</x-app-layout>
