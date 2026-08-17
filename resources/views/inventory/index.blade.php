<x-app-layout>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Quản lý tồn kho</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tồn kho tính theo từng biến thể size/màu. Dưới {{ $threshold }} sản phẩm là sắp hết hàng.
                </p>
            </div>

            {{-- Số liệu tổng quan --}}
            <div class="grid grid-cols-2 gap-4 mb-4 lg:grid-cols-4">
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Tổng tồn</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($summary['total_quantity'], 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $summary['total_variants'] }} biến thể
                    </div>
                </div>
                <div class="p-4 rounded-lg bg-yellow-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Sắp hết hàng</div>
                    <div class="text-2xl font-bold text-yellow-500">{{ $summary['low_stock'] }}</div>
                </div>
                <div class="p-4 rounded-lg bg-red-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Hết hàng</div>
                    <div class="text-2xl font-bold text-red-600">{{ $summary['out_of_stock'] }}</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Giá trị tồn kho</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($summary['stock_value'], 0, ',', '.') }} ₫</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">theo giá nhập</div>
                </div>
            </div>

            {{-- Bộ lọc --}}
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" id="filter-keyword" placeholder="Tìm theo tên sản phẩm hoặc SKU"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <select id="filter-category"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <select id="filter-stock"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 sm:w-56 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Tất cả tình trạng</option>
                    <option value="low">Sắp hết hàng</option>
                    <option value="out">Hết hàng</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Sản phẩm</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Biến thể</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    SKU</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Tồn</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Tình trạng</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Giá trị</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('inventory.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="inventoryPagination">
        {{ $variants->links('vendor.pagination.tailwind') }}
    </div>

    {{-- Ngăn điều chỉnh tồn kho --}}
    <div id="drawer-adjust-stock"
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-hidden="true">
        <h5 class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Điều chỉnh tồn kho</h5>
        <button type="button" id="closeDrawerAdjust"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Đóng</span>
        </button>

        <form id="formAdjust" class="space-y-4">
            @csrf
            <p id="adjust-label" class="text-sm font-medium text-gray-900 dark:text-white"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Tồn hiện tại: <span id="adjust-current"
                    class="font-bold"></span></p>

            <div>
                <label for="adjust-quantity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Số
                    lượng thực tế</label>
                <input type="number" min="0" id="adjust-quantity" name="quantity" required
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nhập số lượng sau khi kiểm, không phải mức
                    chênh lệch.</p>
            </div>

            <div>
                <label for="adjust-reason"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lý do</label>
                <select id="adjust-reason" name="reason" required
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($reasons as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="adjust-note" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ghi
                    chú</label>
                <textarea id="adjust-note" name="note" rows="3"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>

            <button type="submit"
                class="w-full text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg text-sm px-5 py-2.5">
                Lưu điều chỉnh
            </button>
        </form>
    </div>

    {{-- Ngăn lịch sử điều chỉnh --}}
    <div id="drawer-stock-history"
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-md p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-hidden="true">
        <h5 class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Lịch sử điều chỉnh</h5>
        <button type="button" id="closeDrawerHistory"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Đóng</span>
        </button>
        <p id="history-label" class="mb-4 text-sm font-medium text-gray-900 dark:text-white"></p>
        <div id="history-body" class="space-y-3"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let adjustingVariantId = null;

            const showAjaxError = (xhr) => {
                const res = xhr.responseJSON || {};
                if (res.errors) {
                    alert(Object.keys(res.errors).map(k => res.errors[k].join('\n')).join('\n'));
                } else {
                    alert(res.error || res.message || 'Lỗi: ' + xhr.statusText);
                }
            };

            const openDrawer = (id) => $('#' + id).removeClass('translate-x-full').attr('aria-hidden', 'false');
            const closeDrawer = (id) => $('#' + id).addClass('translate-x-full').attr('aria-hidden', 'true');

            $('#closeDrawerAdjust').click(() => closeDrawer('drawer-adjust-stock'));
            $('#closeDrawerHistory').click(() => closeDrawer('drawer-stock-history'));

            const reloadInventory = () => {
                $.ajax({
                    url: '{{ route('inventory.data') }}',
                    type: 'GET',
                    data: {
                        keyword: $('#filter-keyword').val(),
                        categories_id: $('#filter-category').val(),
                        stock: $('#filter-stock').val()
                    },
                    success: function(data) {
                        $('#inventoryTable').html(data);
                        // Phân trang không còn khớp sau khi lọc bằng AJAX nên ẩn đi.
                        $('#inventoryPagination').hide();
                    }
                });
            };

            $('#filter-keyword').on('input', reloadInventory);
            $('#filter-category, #filter-stock').on('change', reloadInventory);

            $(document).on('click', '.adjustStockButton', function() {
                adjustingVariantId = $(this).data('variant-id');
                $('#adjust-label').text($(this).data('label'));
                $('#adjust-current').text($(this).data('quantity'));
                $('#adjust-quantity').val($(this).data('quantity'));
                $('#adjust-note').val('');
                openDrawer('drawer-adjust-stock');
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
                        alert(response.success);
                        closeDrawer('drawer-adjust-stock');
                        reloadInventory();
                    },
                    error: showAjaxError
                });
            });

            $(document).on('click', '.historyStockButton', function() {
                $('#history-label').text($(this).data('label'));
                $('#history-body').html(
                    '<p class="text-sm text-gray-500 dark:text-gray-400">Đang tải...</p>');
                openDrawer('drawer-stock-history');

                $.ajax({
                    url: '/inventory/history/' + $(this).data('variant-id'),
                    type: 'GET',
                    success: function(rows) {
                        if (!rows.length) {
                            $('#history-body').html(
                                '<p class="text-sm text-gray-500 dark:text-gray-400">Chưa có lần điều chỉnh nào.</p>'
                            );
                            return;
                        }
                        const html = rows.map(r => `
                            <div class="p-3 text-sm border border-gray-200 rounded-lg dark:border-gray-700">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white">${r.reason}</span>
                                    <span class="font-bold ${r.change > 0 ? 'text-green-600' : 'text-red-600'}">
                                        ${r.change > 0 ? '+' : ''}${r.change}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    ${r.before} → ${r.after} · ${r.time} · ${r.user}
                                </div>
                                ${r.note ? `<div class="mt-1 text-xs text-gray-600 dark:text-gray-300">${r.note}</div>` : ''}
                            </div>
                        `).join('');
                        $('#history-body').html(html);
                    },
                    error: showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
