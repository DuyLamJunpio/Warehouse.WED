<x-app-layout>
    <div class="p-4 bg-white border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Bán tại quầy</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Dành cho khách mua trực tiếp tại cửa hàng. Đơn lập xong là hoàn thành và đã thu tiền, trừ kho ngay.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 p-4 lg:grid-cols-3">

        {{-- Cột trái: chọn hàng --}}
        <div class="lg:col-span-2">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <label for="pos-search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Tìm sản phẩm
                </label>
                <input type="text" id="pos-search" autocomplete="off"
                    placeholder="Gõ tên sản phẩm hoặc SKU, rồi bấm vào dòng để thêm"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                <div id="pos-results" class="mt-3 overflow-y-auto max-h-72"></div>
            </div>

            <div class="mt-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="p-3 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                Sản phẩm</th>
                            <th class="p-3 text-xs font-medium text-center text-gray-500 uppercase dark:text-gray-400">
                                Số lượng</th>
                            <th class="p-3 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">
                                Đơn giá</th>
                            <th class="p-3 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">
                                Thành tiền</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody id="pos-cart" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
                <p id="pos-empty" class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
                    Chưa chọn sản phẩm nào.
                </p>
            </div>
        </div>

        {{-- Cột phải: thanh toán --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800 h-fit">
            <h2 class="mb-3 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">Thanh toán</h2>

            <div class="space-y-3">
                <div>
                    <label for="pos-phone" class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">
                        Số điện thoại khách <span class="text-xs font-normal text-gray-500">(không bắt buộc)</span>
                    </label>
                    <input type="text" id="pos-phone" placeholder="Bỏ trống nếu khách vãng lai"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nhập số điện thoại để cộng dồn vào hồ sơ
                        khách quen.</p>
                </div>

                <div>
                    <label for="pos-name"
                        class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Tên khách</label>
                    <input type="text" id="pos-name" placeholder="Khách lẻ"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="pos-payment"
                        class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Hình thức</label>
                    <select id="pos-payment"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @foreach ($paymentMethods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pos-discount"
                        class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Giảm giá (đ)</label>
                    <input type="number" id="pos-discount" min="0" value="0"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="pos-note"
                        class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ghi chú</label>
                    <textarea id="pos-note" rows="2"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>
            </div>

            <div class="pt-3 mt-4 space-y-1 text-sm border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Tiền hàng</span><span id="pos-subtotal">0 ₫</span>
                </div>
                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Giảm giá</span><span id="pos-discount-view">0 ₫</span>
                </div>
                <div class="flex justify-between pt-2 text-lg font-bold text-gray-900 dark:text-white">
                    <span>Khách trả</span><span id="pos-total">0 ₫</span>
                </div>
            </div>

            <button type="button" id="pos-submit"
                class="w-full mt-4 px-5 py-3 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 disabled:opacity-50">
                Lập đơn &amp; thu tiền
            </button>
            <button type="button" id="pos-clear"
                class="w-full mt-2 px-5 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                Xoá hết
            </button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Giỏ hàng tại quầy: variantId -> { product, label, price, stock, qty }
            const cart = new Map();
            const money = (n) => new Intl.NumberFormat('vi-VN').format(n) + ' ₫';

            const showAjaxError = (xhr) => {
                const res = xhr.responseJSON || {};
                if (res.errors) {
                    alert(Object.keys(res.errors).map(k => res.errors[k].join('\n')).join('\n'));
                } else {
                    alert(res.error || res.message || 'Lỗi: ' + xhr.statusText);
                }
            };

            const renderCart = () => {
                const rows = [];
                let subtotal = 0;

                cart.forEach((item, id) => {
                    const lineTotal = item.price * item.qty;
                    subtotal += lineTotal;
                    rows.push(`
                        <tr>
                            <td class="p-3 text-sm">
                                <div class="font-medium text-gray-900 dark:text-white">${item.product}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">${item.label} · còn ${item.stock}</div>
                            </td>
                            <td class="p-3 text-center">
                                <input type="number" min="1" max="${item.stock}" value="${item.qty}"
                                    data-id="${id}"
                                    class="pos-qty w-20 text-sm text-center rounded-lg bg-gray-50 border-gray-300 p-1.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </td>
                            <td class="p-3 text-sm text-right text-gray-900 dark:text-white">${money(item.price)}</td>
                            <td class="p-3 text-sm font-medium text-right text-gray-900 dark:text-white">${money(lineTotal)}</td>
                            <td class="p-3 text-right">
                                <button type="button" data-id="${id}"
                                    class="pos-remove px-2 py-1 text-sm text-white bg-red-700 rounded hover:bg-red-800">×</button>
                            </td>
                        </tr>`);
                });

                $('#pos-cart').html(rows.join(''));
                $('#pos-empty').toggle(cart.size === 0);

                // Giảm giá không được vượt tiền hàng, nếu không "khách trả" thành số âm.
                let discount = Math.max(0, parseInt($('#pos-discount').val() || '0', 10) || 0);
                if (discount > subtotal) {
                    discount = subtotal;
                    $('#pos-discount').val(discount);
                }

                $('#pos-subtotal').text(money(subtotal));
                $('#pos-discount-view').text('-' + money(discount));
                $('#pos-total').text(money(subtotal - discount));
                $('#pos-submit').prop('disabled', cart.size === 0);
            };

            const addToCart = (v) => {
                const existing = cart.get(v.id);
                const qty = (existing ? existing.qty : 0) + 1;

                if (qty > v.stock) {
                    alert(`${v.product} (${v.label}) chỉ còn ${v.stock} sản phẩm.`);
                    return;
                }
                cart.set(v.id, Object.assign({}, v, { qty }));
                renderCart();
            };

            let searchTimer = null;
            const doSearch = () => {
                $.ajax({
                    url: '{{ route('pos.search') }}',
                    type: 'GET',
                    data: {
                        keyword: $('#pos-search').val()
                    },
                    success: function(list) {
                        if (!list.length) {
                            $('#pos-results').html(
                                '<p class="p-3 text-sm text-gray-500 dark:text-gray-400">Không tìm thấy sản phẩm còn hàng.</p>'
                            );
                            return;
                        }
                        $('#pos-results').html(list.map(v => `
                            <button type="button" data-variant='${JSON.stringify(v)}'
                                class="pos-pick flex items-center justify-between w-full p-2 text-left rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${v.product}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">${v.label} · ${v.sku} · còn ${v.stock}</span>
                                </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${money(v.price)}</span>
                            </button>`).join(''));
                    },
                    error: showAjaxError
                });
            };

            $('#pos-search').on('input focus', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(doSearch, 250);
            });

            $(document).on('click', '.pos-pick', function() {
                addToCart($(this).data('variant'));
            });

            $(document).on('input', '.pos-qty', function() {
                const id = $(this).data('id');
                const item = cart.get(id);
                if (!item) return;

                let qty = parseInt($(this).val() || '1', 10) || 1;
                if (qty > item.stock) {
                    alert(`${item.product} (${item.label}) chỉ còn ${item.stock} sản phẩm.`);
                    qty = item.stock;
                }
                item.qty = Math.max(1, qty);
                renderCart();
            });

            $(document).on('click', '.pos-remove', function() {
                cart.delete($(this).data('id'));
                renderCart();
            });

            $('#pos-discount').on('input', renderCart);

            $('#pos-clear').click(function() {
                cart.clear();
                $('#pos-phone, #pos-name, #pos-note').val('');
                $('#pos-discount').val(0);
                renderCart();
            });

            $('#pos-submit').click(function() {
                if (cart.size === 0) return;
                const button = $(this).prop('disabled', true);

                $.ajax({
                    url: '{{ route('pos.store') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        // Chỉ gửi id biến thể và số lượng, giá do máy chủ tự tính.
                        items: [...cart.entries()].map(([id, item]) => ({
                            variant_id: id,
                            quantity: item.qty
                        })),
                        customer_phone: $('#pos-phone').val(),
                        customer_name: $('#pos-name').val(),
                        payment_method: $('#pos-payment').val(),
                        discount: $('#pos-discount').val() || 0,
                        note: $('#pos-note').val()
                    },
                    success: function(res) {
                        if (confirm(res.success + '\n\nIn phiếu cho khách?')) {
                            window.open(res.print_url, '_blank');
                        }
                        $('#pos-clear').click();
                        $('#pos-search').val('').focus();
                        $('#pos-results').empty();
                    },
                    error: showAjaxError,
                    complete: () => button.prop('disabled', false)
                });
            });

            renderCart();
        });
    </script>
</x-app-layout>
