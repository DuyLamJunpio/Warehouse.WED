<x-app-layout>
    {{-- Header & Breadcrumb --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                        <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                        </li>
                        <li>
                            <span class="mx-1 text-slate-400">/</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Đơn hàng</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý đơn hàng
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btn-toggle-create"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span id="btn-toggle-create-label">Bán hàng (POS)</span>
                    <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono bg-indigo-700 text-indigo-200 rounded">F2</kbd>
                </button>
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
        <x-stat-card label="Tổng đơn hàng" :value="$summary['total_orders']" color="neutral" subtitle="Tất cả đơn đã tạo">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Chờ xử lý" :value="$summary['by_status']['pending'] ?? 0" color="amber" subtitle="Cần duyệt & đóng gói">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Đang giao hàng" :value="$summary['by_status']['shipping'] ?? 0" color="indigo" subtitle="Đang vận chuyển">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Đã hoàn thành" :value="$summary['by_status']['completed'] ?? 0" color="emerald" subtitle="Giao & nhận đủ">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Đã hoàn / Hủy" :value="($summary['by_status']['returned'] ?? 0) + ($summary['by_status']['cancelled'] ?? 0)" color="rose" subtitle="Hủy hoặc trả hàng">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Doanh thu thực" :value="number_format($summary['revenue'], 0, ',', '.') . ' ₫'" color="emerald" subtitle="Đơn hoàn tất">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>


    {{-- POS Drawer Panel Include --}}
    @include('order.create')

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-4 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            {{-- Search & Status Dropdown --}}
            <div class="flex flex-1 flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="filter-keyword"
                        class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                        placeholder="Tìm theo mã đơn, tên khách, SĐT...">
                    <button type="button" id="clearKeyword" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="w-full sm:w-56">
                    <select id="filter-status"
                        class="block w-full py-2 px-3 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white">
                        <option value="">Tất cả trạng thái</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }} ({{ $summary['by_status'][$key] ?? 0 }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" data-status-pill="" class="status-filter-pill px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 transition-all">
                    Tất cả ({{ $summary['total_orders'] }})
                </button>
                <button type="button" data-status-pill="pending" class="status-filter-pill px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Chờ xử lý ({{ $summary['by_status']['pending'] ?? 0 }})
                </button>
                <button type="button" data-status-pill="shipping" class="status-filter-pill px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Đang giao ({{ $summary['by_status']['shipping'] ?? 0 }})
                </button>
                <button type="button" data-status-pill="completed" class="status-filter-pill px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Đã xong ({{ $summary['by_status']['completed'] ?? 0 }})
                </button>
            </div>

        </div>
    </div>

    {{-- Order Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-4">Mã đơn &amp; Thời gian</th>
                        <th scope="col" class="p-4">Người nhận / SĐT</th>
                        <th scope="col" class="p-4">Số lượng</th>
                        <th scope="col" class="p-4">Tổng tiền</th>
                        <th scope="col" class="p-4">Trạng thái</th>
                        <th scope="col" class="p-4">Thanh toán</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="orderTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('order.data')
                </tbody>
            </table>
        </div>

        <div id="orderPagination">
            {{ $orders->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- DRAWER: CHI TIẾT ĐƠN HÀNG (ORDER DETAIL) --}}
    <div id="drawer-order-detail" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-xl h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Chi tiết đơn hàng</h3>
            </div>
            <button type="button" id="closeDrawerOrder" data-drawer-dismiss="drawer-order-detail" data-drawer-hide="drawer-order-detail"
                class="btn-close-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">
                ✕
            </button>
        </div>

        {{-- Body Content --}}
        <div class="p-6 space-y-6 flex-1 overflow-y-auto custom-scrollbar" id="order-detail-body">
            {{-- Loaded via AJAX --}}
        </div>

        {{-- Status Transition Footer --}}
        <div id="order-status-box" class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 sticky bottom-0">
            <label for="order-next-status" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                Chuyển trạng thái đơn
            </label>
            <div class="flex items-center gap-3">
                <select id="order-next-status"
                    class="block w-full text-sm rounded-xl bg-white border-slate-300 p-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></select>
                <button type="button" id="btn-update-status"
                    class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 whitespace-nowrap shadow-sm transition-all">
                    Cập nhật
                </button>
            </div>
            <p id="order-status-hint" class="mt-2 text-xs text-slate-500 dark:text-slate-400"></p>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            let currentOrderId = null;
            const money = (n) => window.nhomNghin(n) + ' ₫';

            const openDrawer = () => window.openDrawer('drawer-order-detail');
            const closeDrawer = () => window.closeDrawer('drawer-order-detail');


            const reloadOrders = () => {
                $.ajax({
                    url: '{{ route('order.data') }}',
                    type: 'GET',
                    data: {
                        keyword: $('#filter-keyword').val(),
                        order_status: $('#filter-status').val()
                    },
                    success: function(data) {
                        $('#orderTable').html(data);
                    }
                });
            };

            // Search with Debounce
            $('#filter-keyword').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearKeyword').removeClass('hidden');
                else $('#clearKeyword').addClass('hidden');
                reloadOrders();
            }, 300));

            $('#clearKeyword').on('click', function() {
                $('#filter-keyword').val('');
                $(this).addClass('hidden');
                reloadOrders();
            });

            $('#filter-status').on('change', reloadOrders);

            $('.status-filter-pill').on('click', function() {
                $('.status-filter-pill').removeClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').addClass('text-slate-600 font-medium');
                $(this).addClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').removeClass('text-slate-600 font-medium');
                const st = $(this).data('status-pill');
                $('#filter-status').val(st);
                reloadOrders();
            });

            // View Order Detail
            $(document).on('click', '.viewOrderButton', function() {
                currentOrderId = $(this).data('order-id');
                $('#order-detail-body').html('<div class="py-12 text-center text-slate-400 text-sm">Đang tải thông tin đơn hàng...</div>');
                openDrawer();

                $.ajax({
                    url: '/order/' + currentOrderId,
                    type: 'GET',
                    success: function(o) {
                        const items = (o.items || []).map(i => `
                            <tr class="text-xs">
                                <td class="py-2.5">
                                    <div class="font-bold text-slate-900 dark:text-white">${i.product}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">${i.variant}</div>
                                </td>
                                <td class="py-2.5 text-center font-semibold text-slate-800 dark:text-slate-200">${i.quantity}</td>
                                <td class="py-2.5 text-right text-slate-600 dark:text-slate-400">${money(i.unit_price)}</td>
                                <td class="py-2.5 text-right font-bold text-slate-900 dark:text-white">${money(i.line_total)}</td>
                            </tr>
                        `).join('');

                        $('#order-detail-body').html(`
                            {{-- Header summary card --}}
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-700 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-bold text-slate-900 dark:text-white">${o.order_code || '#' + o.id}</span>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                        ${o.status_label || 'Chưa có trạng thái'}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    Tạo lúc: ${o.created_at} · Người lập: ${o.seller || 'Hệ thống'}
                                </div>
                            </div>

                            {{-- Customer Information --}}
                            <div class="space-y-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Thông tin người nhận</div>
                                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-700/30 border border-slate-200/80 dark:border-slate-700 text-xs space-y-1">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">${o.shipping_name || o.customer || 'Khách lẻ'}</div>
                                    <div class="text-slate-600 dark:text-slate-300">📞 ${o.shipping_phone || 'Chưa cung cấp SĐT'}</div>
                                    <div class="text-slate-600 dark:text-slate-300">📍 ${o.shipping_address || 'Nhận trực tiếp tại cửa hàng'}</div>
                                    <div class="text-slate-600 dark:text-slate-300">💳 Thanh toán: ${o.payment_method || 'Tiền mặt'}</div>
                                </div>
                            </div>

                            {{-- Items Table --}}
                            <div class="space-y-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Danh sách sản phẩm</div>
                                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <table class="w-full text-left p-3">
                                        <thead class="bg-slate-50 dark:bg-slate-700/60 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                            <tr>
                                                <th class="py-2 px-3">Sản phẩm</th>
                                                <th class="py-2 text-center">SL</th>
                                                <th class="py-2 text-right">Đơn giá</th>
                                                <th class="py-2 px-3 text-right">Tổng</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 px-3">
                                            ${items}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Payment Calculation --}}
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-700 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Tiền hàng:</span>
                                    <span class="font-medium text-slate-900 dark:text-white">${money(o.subtotal)}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Phí giao hàng:</span>
                                    <span class="font-medium text-slate-900 dark:text-white">${money(o.shipping_fee || 0)}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Chiết khấu:</span>
                                    <span class="font-medium text-rose-600">-${money(o.discount || 0)}</span>
                                </div>
                                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-600">
                                    <span>Tổng thanh toán:</span>
                                    <span class="text-base text-indigo-600 dark:text-indigo-400">${money(o.total_amount)}</span>
                                </div>
                            </div>

                            ${o.note ? `<div class="text-xs p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 rounded-xl border border-amber-200 dark:border-amber-800"><strong>Ghi chú:</strong> ${o.note}</div>` : ''}
                        `);

                        const sel = $('#order-next-status').empty();
                        if (o.next_statuses && o.next_statuses.length) {
                            o.next_statuses.forEach(s => sel.append($('<option>').val(s.value).text(s.label)));
                            $('#order-status-box').show();
                            $('#order-status-hint').text(
                                o.next_statuses.some(n => ['cancelled', 'returned'].includes(n.value)) ?
                                '💡 Lưu ý: Hủy đơn hoặc hoàn hàng sẽ tự động cộng trả lại tồn kho sản phẩm.' : ''
                            );
                        } else {
                            $('#order-status-box').hide();
                        }
                    },
                    error: window.showAjaxError
                });
            });

            // Fast Status Change
            const doiTrangThai = ($nut, orderId, status) => {
                if ($nut.prop('disabled')) return;
                const chuCu = $nut.html();
                $nut.prop('disabled', true).addClass('opacity-50 cursor-not-allowed').text('Đang xử lý...');

                return $.ajax({
                    url: '/order/' + orderId + '/status',
                    type: 'POST',
                    data: { order_status: status },
                    success: function(response) {
                        window.showToast(response.success);
                        reloadOrders();
                    },
                    error: function(xhr) {
                        $nut.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').html(chuCu);
                        window.showAjaxError(xhr);
                    }
                });
            };

            $(document).on('click', '.btn-doi-trang-thai', function() {
                const $nut = $(this);
                doiTrangThai($nut, $nut.data('order-id'), $nut.data('status'));
            });

            $('#btn-update-status').click(function() {
                if (!currentOrderId) return;
                const status = $('#order-next-status').val();
                if (!status) return;
                const req = doiTrangThai($(this), currentOrderId, status);
                if (req) req.done(closeDrawer);
            });
        });
    </script>
</x-app-layout>
