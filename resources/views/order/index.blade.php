<x-app-layout>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Quản lý đơn hàng</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tồn kho đã trừ ngay khi lập đơn. Hủy đơn hoặc khách hoàn hàng thì hàng được cộng trả về kho.
                </p>
            </div>

            {{-- Số liệu tổng quan --}}
            <div class="grid grid-cols-2 gap-3 mb-4 lg:grid-cols-4">
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Tổng đơn</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['total_orders'] }}</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Chờ xác nhận</div>
                    <div class="text-2xl font-bold text-yellow-500">{{ $summary['by_status']['pending'] ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Đang giao</div>
                    <div class="text-2xl font-bold text-blue-500">{{ $summary['by_status']['shipping'] ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <div class="text-xs text-gray-500 uppercase dark:text-gray-400">Doanh thu đã hoàn thành</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ number_format($summary['revenue'], 0, ',', '.') }} ₫</div>
                </div>
            </div>

            {{-- Bộ lọc --}}
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" id="filter-keyword" placeholder="Tìm theo mã đơn, tên hoặc số điện thoại"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <select id="filter-status"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }} ({{ $summary['by_status'][$key] ?? 0 }})</option>
                    @endforeach
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
                                    Mã đơn</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Người nhận</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Số lượng</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Tổng tiền</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Trạng thái</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Thanh toán</th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="orderTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('order.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="orderPagination">
        {{ $orders->links('vendor.pagination.tailwind') }}
    </div>

    {{-- Ngăn chi tiết đơn --}}
    <div id="drawer-order-detail"
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-lg p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-hidden="true">
        <h5 class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Chi tiết đơn hàng</h5>
        <button type="button" id="closeDrawerOrder"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Đóng</span>
        </button>

        <div id="order-detail-body"></div>

        <div id="order-status-box" class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <label for="order-next-status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Chuyển
                trạng thái</label>
            <div class="flex gap-2">
                <select id="order-next-status"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></select>
                <button type="button" id="btn-update-status"
                    class="px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 whitespace-nowrap">
                    Cập nhật
                </button>
            </div>
            <p id="order-status-hint" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentOrderId = null;

            const money = (n) => new Intl.NumberFormat('vi-VN').format(n) + ' ₫';

            const showAjaxError = (xhr) => {
                const res = xhr.responseJSON || {};
                if (res.errors) {
                    alert(Object.keys(res.errors).map(k => res.errors[k].join('\n')).join('\n'));
                } else {
                    alert(res.error || res.message || 'Lỗi: ' + xhr.statusText);
                }
            };

            const openDrawer = () => $('#drawer-order-detail').removeClass('translate-x-full').attr('aria-hidden',
                'false');
            const closeDrawer = () => $('#drawer-order-detail').addClass('translate-x-full').attr('aria-hidden',
                'true');
            $('#closeDrawerOrder').click(closeDrawer);

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
                        // Phân trang không còn khớp sau khi lọc bằng AJAX nên ẩn đi.
                        $('#orderPagination').hide();
                    }
                });
            };

            $('#filter-keyword').on('input', reloadOrders);
            $('#filter-status').on('change', reloadOrders);

            $(document).on('click', '.viewOrderButton', function() {
                currentOrderId = $(this).data('order-id');
                $('#order-detail-body').html(
                    '<p class="text-sm text-gray-500 dark:text-gray-400">Đang tải...</p>');
                openDrawer();

                $.ajax({
                    url: '/order/' + currentOrderId,
                    type: 'GET',
                    success: function(o) {
                        const items = o.items.map(i => `
                            <tr>
                                <td class="py-2 text-sm text-gray-900 dark:text-white">
                                    ${i.product}<div class="text-xs text-gray-500 dark:text-gray-400">${i.variant}</div>
                                </td>
                                <td class="py-2 text-sm text-center text-gray-900 dark:text-white">${i.quantity}</td>
                                <td class="py-2 text-sm text-right text-gray-900 dark:text-white">${money(i.unit_price)}</td>
                                <td class="py-2 text-sm text-right text-gray-900 dark:text-white">${money(i.line_total)}</td>
                            </tr>`).join('');

                        $('#order-detail-body').html(`
                            <div class="mb-4">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">${o.order_code || '#' + o.id}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">${o.created_at} · ${o.status_label || 'Chưa có trạng thái'} · NV: ${o.seller || '—'}</div>
                            </div>
                            <div class="p-3 mb-4 text-sm rounded-lg bg-gray-50 dark:bg-gray-700">
                                <div class="font-medium text-gray-900 dark:text-white">${o.shipping_name || o.customer || 'Khách lẻ'}</div>
                                <div class="text-gray-500 dark:text-gray-400">${o.shipping_phone || '—'}</div>
                                <div class="text-gray-500 dark:text-gray-400">${o.shipping_address || 'Không có địa chỉ giao'}</div>
                                <div class="mt-1 text-gray-500 dark:text-gray-400">Thanh toán: ${o.payment_method || '—'}</div>
                            </div>
                            <table class="w-full mb-4">
                                <thead>
                                    <tr class="text-xs text-gray-500 uppercase border-b dark:text-gray-400 dark:border-gray-700">
                                        <th class="py-2 text-left">Sản phẩm</th>
                                        <th class="py-2 text-center">SL</th>
                                        <th class="py-2 text-right">Đơn giá</th>
                                        <th class="py-2 text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y dark:divide-gray-700">${items}</tbody>
                            </table>
                            <div class="text-sm text-right text-gray-900 dark:text-white">
                                <div>Tiền hàng: ${money(o.subtotal)}</div>
                                <div>Phí giao: ${money(o.shipping_fee)}</div>
                                <div>Chiết khấu: ${money(o.discount)}</div>
                                <div class="mt-1 text-lg font-bold">Tổng: ${money(o.total_amount)}</div>
                            </div>
                            ${o.note ? `<p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Ghi chú: ${o.note}</p>` : ''}
                        `);

                        // Chỉ hiện những trạng thái server cho phép chuyển sang.
                        const sel = $('#order-next-status').empty();
                        if (o.next_statuses.length) {
                            o.next_statuses.forEach(s => sel.append($('<option>').val(s.value).text(s
                                .label)));
                            $('#order-status-box').show();
                            $('#order-status-hint').text(
                                o.next_statuses.some(n => ['cancelled', 'returned'].includes(n
                                    .value)) ?
                                'Hủy đơn hoặc hoàn hàng sẽ cộng trả hàng về kho.' : '');
                        } else {
                            $('#order-status-box').hide();
                        }
                    },
                    error: showAjaxError
                });
            });

            $('#btn-update-status').click(function() {
                if (!currentOrderId) return;
                const status = $('#order-next-status').val();
                if (!status) return;

                $.ajax({
                    url: '/order/' + currentOrderId + '/status',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        order_status: status
                    },
                    success: function(response) {
                        alert(response.success);
                        closeDrawer();
                        reloadOrders();
                    },
                    error: showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
