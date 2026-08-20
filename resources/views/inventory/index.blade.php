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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Tồn kho</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý tồn kho &amp; Kiểm kê
                </h1>
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
        <x-stat-card label="Tổng sản phẩm tồn" :value="number_format($summary['total_quantity'], 0, ',', '.')" color="indigo">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </x-slot:icon>
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $summary['total_variants'] }} biến thể</span>
        </x-stat-card>

        <x-stat-card label="Sắp hết hàng (≤ {{ $threshold }})" :value="$summary['low_stock']" color="amber">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </x-slot:icon>
            <span class="text-xs text-amber-600 dark:text-amber-400">Cần nhập thêm</span>
        </x-stat-card>

        <x-stat-card label="Hết hàng (0)" :value="$summary['out_of_stock']" color="rose">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </x-slot:icon>
            <span class="text-xs text-rose-600 dark:text-rose-400">Tạm ngừng bán</span>
        </x-stat-card>

        <x-stat-card label="Tổng giá trị vốn kho" :value="number_format($summary['stock_value'], 0, ',', '.') . ' ₫'" color="emerald">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
            <span class="text-xs text-slate-400 dark:text-slate-500">Tính theo giá nhập</span>
        </x-stat-card>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-4 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            {{-- Search & Category Filter --}}
            <div class="flex flex-1 flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="filter-keyword"
                        class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                        placeholder="Tìm tên sản phẩm, SKU...">
                    <button type="button" id="clearKeyword" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="w-full sm:w-56">
                    <select id="filter-category"
                        class="block w-full py-2 px-3 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" data-stock-filter="" class="stock-filter-pill px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 transition-all">
                    Tất cả ({{ $summary['total_variants'] }})
                </button>
                <button type="button" data-stock-filter="low" class="stock-filter-pill px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Sắp hết ({{ $summary['low_stock'] }})
                </button>
                <button type="button" data-stock-filter="out" class="stock-filter-pill px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Hết hàng ({{ $summary['out_of_stock'] }})
                </button>
            </div>

        </div>
    </div>

    {{-- Inventory Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-4">Sản phẩm &amp; Danh mục</th>
                        <th scope="col" class="p-4">Biến thể (Size/Màu)</th>
                        <th scope="col" class="p-4">Mã SKU</th>
                        <th scope="col" class="p-4">Số lượng tồn</th>
                        <th scope="col" class="p-4">Tình trạng</th>
                        <th scope="col" class="p-4">Giá trị tồn kho</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="inventoryTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('inventory.data')
                </tbody>
            </table>
        </div>

        <div id="inventoryPagination">
            {{ $variants->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: ĐIỀU CHỈNH TỒN KHO (ADJUST STOCK)                                 --}}
    {{-- ========================================================================= --}}
    <div id="drawer-adjust-stock" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Kiểm kê / Điều chỉnh kho</h3>
            </div>
            <button type="button" id="closeDrawerAdjust"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">
                ✕
            </button>
        </div>

        {{-- Form Content --}}
        <form id="formAdjust" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">

                {{-- Target item info --}}
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-700 space-y-1">
                    <div id="adjust-label" class="text-sm font-bold text-slate-900 dark:text-white"></div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                        <span>Tồn kho trên hệ thống:</span>
                        <span id="adjust-current" class="font-bold text-indigo-600 dark:text-indigo-400 text-sm"></span>
                    </div>
                </div>

                {{-- Actual Quantity Input --}}
                <div>
                    <label for="adjust-quantity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Số lượng thực tế sau kiểm đếm <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" min="0" id="adjust-quantity" name="quantity" required
                        class="block w-full text-base font-bold rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    
                    {{-- Difference Preview --}}
                    <div id="adjust-diff-preview" class="mt-2 text-xs flex items-center justify-between font-medium">
                        <span class="text-slate-500">Chênh lệch:</span>
                        <span id="adjust-diff-val" class="font-bold">0</span>
                    </div>
                </div>

                {{-- Reason Select --}}
                <div>
                    <label for="adjust-reason" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Lý do điều chỉnh <span class="text-rose-500">*</span>
                    </label>
                    <select id="adjust-reason" name="reason" required
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        @foreach ($reasons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Note Textarea --}}
                <div>
                    <label for="adjust-note" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Ghi chú chi tiết
                    </label>
                    <textarea id="adjust-note" name="note" rows="3" placeholder="Nhập lý do kiểm đếm, người chịu trách nhiệm..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

            </div>

            {{-- Sticky Footer --}}
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" data-drawer-dismiss="drawer-adjust-stock"
                    class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    Lưu điều chỉnh
                </button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: LỊCH SỬ ĐIỀU CHỈNH TỒN KHO (STOCK HISTORY)                        --}}
    {{-- ========================================================================= --}}
    <div id="drawer-stock-history" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Lịch sử kiểm kê</h3>
            </div>
            <button type="button" id="closeDrawerHistory"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">
                ✕
            </button>
        </div>

        {{-- Body Content --}}
        <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
            <p id="history-label" class="text-xs font-semibold text-slate-500 dark:text-slate-400 pb-2 border-b border-slate-100 dark:border-slate-700"></p>
            <div id="history-body" class="space-y-3">
                {{-- Audit rows loaded via AJAX --}}
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            let adjustingVariantId = null;
            let currentStockQty = 0;
            let stockFilter = '';

            const openDrawer = (id) => $('#' + id).removeClass('translate-x-full');
            const closeDrawer = (id) => $('#' + id).addClass('translate-x-full');

            $('#closeDrawerAdjust, [data-drawer-dismiss="drawer-adjust-stock"]').click(() => closeDrawer('drawer-adjust-stock'));
            $('#closeDrawerHistory').click(() => closeDrawer('drawer-stock-history'));

            const reloadInventory = () => {
                $.ajax({
                    url: '{{ route('inventory.data') }}',
                    type: 'GET',
                    data: {
                        keyword: $('#filter-keyword').val(),
                        categories_id: $('#filter-category').val(),
                        stock: stockFilter
                    },
                    success: function(data) {
                        $('#inventoryTable').html(data);
                    }
                });
            };

            // Search with debounce
            $('#filter-keyword').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearKeyword').removeClass('hidden');
                else $('#clearKeyword').addClass('hidden');
                reloadInventory();
            }, 300));

            $('#clearKeyword').on('click', function() {
                $('#filter-keyword').val('');
                $(this).addClass('hidden');
                reloadInventory();
            });

            $('#filter-category').on('change', reloadInventory);

            $('.stock-filter-pill').on('click', function() {
                $('.stock-filter-pill').removeClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').addClass('text-slate-600 font-medium');
                $(this).addClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').removeClass('text-slate-600 font-medium');
                stockFilter = $(this).data('stock-filter');
                reloadInventory();
            });

            // Adjust Stock Modal Trigger
            $(document).on('click', '.adjustStockButton', function() {
                adjustingVariantId = $(this).data('variant-id');
                currentStockQty = parseInt($(this).data('quantity') || '0', 10);
                
                $('#adjust-label').text($(this).data('label'));
                $('#adjust-current').text(currentStockQty);
                $('#adjust-quantity').val(currentStockQty);
                $('#adjust-note').val('');
                updateDiffPreview(currentStockQty);
                openDrawer('drawer-adjust-stock');
            });

            const updateDiffPreview = (newVal) => {
                const diff = newVal - currentStockQty;
                const diffEl = $('#adjust-diff-val');
                if (diff > 0) {
                    diffEl.text(`+${diff} (Tăng thêm)`).removeClass('text-rose-600 text-slate-500').addClass('text-emerald-600');
                } else if (diff < 0) {
                    diffEl.text(`${diff} (Giảm bớt)`).removeClass('text-emerald-600 text-slate-500').addClass('text-rose-600');
                } else {
                    diffEl.text('0 (Không thay đổi)').removeClass('text-emerald-600 text-rose-600').addClass('text-slate-500');
                }
            };

            $('#adjust-quantity').on('input', function() {
                const val = parseInt($(this).val() || '0', 10);
                updateDiffPreview(val);
            });

            $('#formAdjust').submit(function(e) {
                e.preventDefault();
                if (!adjustingVariantId) return;

                $.ajax({
                    url: '/inventory/adjust/' + adjustingVariantId,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $(this).serialize(),
                    success: function(response) {
                        window.showToast(response.success);
                        closeDrawer('drawer-adjust-stock');
                        reloadInventory();
                    },
                    error: window.showAjaxError
                });
            });

            // Stock History Modal Trigger
            $(document).on('click', '.historyStockButton', function() {
                $('#history-label').text($(this).data('label'));
                $('#history-body').html('<div class="py-12 text-center text-slate-400 text-xs">Đang tải lịch sử điều chỉnh...</div>');
                openDrawer('drawer-stock-history');

                $.ajax({
                    url: '/inventory/history/' + $(this).data('variant-id'),
                    type: 'GET',
                    success: function(rows) {
                        if (!rows.length) {
                            $('#history-body').html('<div class="p-6 text-center text-slate-400 text-xs">Chưa có lịch sử điều chỉnh nào cho biến thể này.</div>');
                            return;
                        }
                        const html = rows.map(r => `
                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-700/40 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 dark:text-white">${r.reason}</span>
                                    <span class="px-2 py-0.5 rounded-md font-bold text-xs ${r.change > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'}">
                                        ${r.change > 0 ? '+' : ''}${r.change}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                    <span>${r.before} ➔ ${r.after}</span>
                                    <span>${r.time} · ${r.user}</span>
                                </div>
                                ${r.note ? `<div class="text-[11px] text-slate-600 dark:text-slate-300 pt-1 border-t border-slate-100 dark:border-slate-700">${r.note}</div>` : ''}
                            </div>
                        `).join('');
                        $('#history-body').html(html);
                    },
                    error: window.showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
