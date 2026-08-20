{{-- Bảng lập đơn POS cho khách mua trực tiếp tại quầy --}}
<div id="order-create-panel"
    class="hidden mb-6 p-5 rounded-2xl border border-indigo-100 bg-indigo-50/40 dark:bg-slate-800/90 dark:border-slate-700 shadow-sm transition-all">

    <div class="flex items-center justify-between pb-3 mb-4 border-b border-indigo-100 dark:border-slate-700">
        <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-indigo-600 text-white shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bán hàng tại quầy (POS)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tạo đơn trực tiếp, tự động trừ tồn kho và hoàn tất tức thì.</p>
            </div>
        </div>
        <button type="button" id="btn-close-pos" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
            ✕ Đóng POS
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- CỘT TRÁI: TÌM SẢN PHẨM & GIỎ HÀNG (8/12) --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            {{-- Search Box --}}
            <div class="relative bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-xs">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="co-search" autocomplete="off"
                        placeholder="Tìm tên sản phẩm hoặc mã SKU (Enter để chọn nhanh)..."
                        class="block w-full pl-9 pr-4 py-2 text-sm rounded-lg bg-slate-50 border-slate-200 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                {{-- Dropdown Instant Results --}}
                <div id="co-results" class="hidden absolute top-full left-0 right-0 mt-1 z-30 max-h-72 overflow-y-auto bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700 custom-scrollbar"></div>
            </div>

            {{-- Cart Items Table --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 dark:bg-slate-800/75 border-b border-slate-200/80 dark:border-slate-700/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="p-3">Sản phẩm</th>
                                <th class="p-3 text-center w-28">Số lượng</th>
                                <th class="p-3 text-right">Đơn giá</th>
                                <th class="p-3 text-right">Thành tiền</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="co-cart" class="divide-y divide-slate-100 dark:divide-slate-700 text-sm"></tbody>
                    </table>
                </div>

                <div id="co-empty" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Giỏ hàng đang trống. Gõ tìm kiếm sản phẩm ở trên để thêm vào giỏ.
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: THÔNG TIN KHÁCH & THANH TOÁN (4/12) --}}
        <div class="lg:col-span-5 xl:col-span-4 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Thông tin thanh toán
            </h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Số điện thoại khách hàng
                    </label>
                    <input type="tel" id="co-phone" placeholder="09xxxx (khách quen)"
                        class="block w-full text-xs rounded-lg bg-slate-50 border-slate-200 px-3 py-2 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tên khách hàng
                    </label>
                    <input type="text" id="co-name" placeholder="Khách lẻ tại quầy"
                        class="block w-full text-xs rounded-lg bg-slate-50 border-slate-200 px-3 py-2 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Hình thức thanh toán
                    </label>
                    <select id="co-payment"
                        class="block w-full text-xs font-medium rounded-lg bg-slate-50 border-slate-200 px-3 py-2 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="cash">💵 Tiền mặt</option>
                        <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                        <option value="credit_card">💳 Quẹt thẻ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Chiết khấu / Giảm giá (VNĐ)
                    </label>
                    <input type="text" inputmode="numeric" id="co-discount" value="0" placeholder="0"
                        class="o-tien block w-full text-xs rounded-lg bg-slate-50 border-slate-200 px-3 py-2 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Ghi chú đơn hàng
                    </label>
                    <input type="text" id="co-note" placeholder="VD: Khách lấy túi to, đổi size trong 3 ngày..."
                        class="block w-full text-xs rounded-lg bg-slate-50 border-slate-200 px-3 py-2 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>
            </div>

            {{-- Summary & Calculator --}}
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Tổng tiền hàng:</span>
                    <span id="co-subtotal" class="font-semibold text-slate-900 dark:text-white">0 ₫</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Giảm giá:</span>
                    <span id="co-discount-view" class="font-semibold text-rose-600">-0 ₫</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-700">
                    <span>Khách cần trả:</span>
                    <span id="co-total" class="text-base text-indigo-600 dark:text-indigo-400">0 ₫</span>
                </div>

                {{-- Cash Tendered & Change --}}
                <div class="pt-2">
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Tiền khách đưa (tính tiền thối):</label>
                    <input type="text" inputmode="numeric" id="co-cash-tendered" placeholder="0"
                        class="o-tien block w-full text-xs rounded-lg bg-slate-50 border-slate-200 px-3 py-1.5 focus:bg-white dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <div class="flex justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                        <span>Tiền thối lại:</span>
                        <span id="co-change">0 ₫</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" id="co-submit" disabled
                    class="w-full py-3 text-sm font-bold text-white rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all">
                    Hoàn thành &amp; Thu tiền (F9)
                </button>
                <button type="button" id="co-clear"
                    class="w-full py-2 text-xs font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Làm mới giỏ hàng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const cart = new Map();
        const money = (n) => window.nhomNghin(n) + ' ₫';
        const panel = $('#order-create-panel');

        $('#btn-toggle-create, #btn-topbar-pos, #btn-close-pos').click(function() {
            panel.toggleClass('hidden');
            const opened = !panel.hasClass('hidden');
            $('#btn-toggle-create-label').text(opened ? 'Đóng POS' : 'Bán hàng (POS)');
            if (opened) {
                $('html, body').animate({ scrollTop: panel.offset().top - 70 }, 200);
                $('#co-search').focus();
            }
        });

        // Global hotkey F2 for POS, F9 for checkout
        $(document).on('keydown', function(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                $('#btn-toggle-create').click();
            }
            if (e.key === 'F9' && !panel.hasClass('hidden') && cart.size > 0) {
                e.preventDefault();
                $('#co-submit').click();
            }
        });

        const renderCart = () => {
            const rows = [];
            let subtotal = 0;

            cart.forEach((item, id) => {
                const lineTotal = item.price * item.qty;
                subtotal += lineTotal;
                rows.push(`
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="p-3">
                            <div class="font-semibold text-slate-900 dark:text-white text-xs">${item.product}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-medium">${item.label}</span>
                                <span>· Còn ${item.stock}</span>
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="btn-qty-minus w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold" data-id="${id}">-</button>
                                <input type="number" min="1" max="${item.stock}" value="${item.qty}" data-id="${id}"
                                    class="co-qty w-12 text-center text-xs font-bold rounded border-slate-200 p-1 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <button type="button" class="btn-qty-plus w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold" data-id="${id}">+</button>
                            </div>
                        </td>
                        <td class="p-3 text-right text-xs text-slate-700 dark:text-slate-300 font-medium">${money(item.price)}</td>
                        <td class="p-3 text-right text-xs font-bold text-slate-900 dark:text-white">${money(lineTotal)}</td>
                        <td class="p-3 text-right">
                            <button type="button" data-id="${id}" class="co-remove p-1 text-slate-400 hover:text-rose-600 rounded">✕</button>
                        </td>
                    </tr>
                `);
            });

            $('#co-cart').html(rows.join(''));
            $('#co-empty').toggle(cart.size === 0);

            // Discount calculation
            const rawDiscount = $('#co-discount').val() ? parseInt($('#co-discount').val().replace(/\D/g, ''), 10) : 0;
            const discount = Math.min(subtotal, Math.max(0, rawDiscount));
            const finalTotal = subtotal - discount;

            $('#co-subtotal').text(money(subtotal));
            $('#co-discount-view').text('-' + money(discount));
            $('#co-total').text(money(finalTotal));
            $('#co-submit').prop('disabled', cart.size === 0);

            // Change calculation
            const cashTendered = $('#co-cash-tendered').val() ? parseInt($('#co-cash-tendered').val().replace(/\D/g, ''), 10) : 0;
            const change = Math.max(0, cashTendered - finalTotal);
            $('#co-change').text(money(change));
        };

        const addToCart = (v) => {
            const existing = cart.get(v.id);
            const qty = (existing ? existing.qty : 0) + 1;

            if (qty > v.stock) {
                window.showToast(`${v.product} (${v.label}) chỉ còn ${v.stock} trong kho.`, 'danger');
                return;
            }
            cart.set(v.id, Object.assign({}, v, { qty }));
            renderCart();
            $('#co-results').addClass('hidden');
            $('#co-search').val('').focus();
        };

        let searchTimer = null;
        const doSearch = () => {
            const kw = $('#co-search').val().trim();
            if (!kw) {
                $('#co-results').addClass('hidden').empty();
                return;
            }

            $.ajax({
                url: '{{ route('order.variants') }}',
                type: 'GET',
                data: { keyword: kw },
                success: function(list) {
                    if (!list.length) {
                        $('#co-results').removeClass('hidden').html('<div class="p-3 text-xs text-slate-400 text-center">Không tìm thấy sản phẩm có sẵn trong kho</div>');
                        return;
                    }
                    $('#co-results').removeClass('hidden').html(list.map(v => `
                        <div class="co-pick flex items-center justify-between p-2.5 hover:bg-indigo-50/60 dark:hover:bg-slate-700/60 cursor-pointer transition-colors" data-variant='${JSON.stringify(v)}'>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">${v.product}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    ${v.label} · <span class="text-indigo-600 dark:text-indigo-400 font-medium">Còn ${v.stock}</span>
                                </div>
                            </div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">${money(v.price)}</div>
                        </div>
                    `).join(''));
                },
                error: window.showAjaxError
            });
        };

        $('#co-search').on('input focus', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(doSearch, 200);
        });

        $(document).on('click', '.co-pick', function() {
            addToCart($(this).data('variant'));
        });

        $(document).on('click', '.btn-qty-plus', function() {
            const id = $(this).data('id');
            const item = cart.get(id);
            if (!item) return;
            if (item.qty >= item.stock) {
                window.showToast(`Chỉ còn ${item.stock} sản phẩm.`, 'danger');
                return;
            }
            item.qty++;
            renderCart();
        });

        $(document).on('click', '.btn-qty-minus', function() {
            const id = $(this).data('id');
            const item = cart.get(id);
            if (!item) return;
            if (item.qty > 1) {
                item.qty--;
            } else {
                cart.delete(id);
            }
            renderCart();
        });

        $(document).on('input', '.co-qty', function() {
            const id = $(this).data('id');
            const item = cart.get(id);
            if (!item) return;

            let qty = parseInt($(this).val() || '1', 10) || 1;
            if (qty > item.stock) {
                window.showToast(`Chỉ còn ${item.stock} sản phẩm.`, 'danger');
                qty = item.stock;
            }
            item.qty = Math.max(1, qty);
            renderCart();
        });

        $(document).on('click', '.co-remove', function() {
            cart.delete($(this).data('id'));
            renderCart();
        });

        $('#co-discount, #co-cash-tendered').on('input', renderCart);

        $('#co-clear').click(function() {
            cart.clear();
            $('#co-phone, #co-name, #co-note, #co-cash-tendered').val('');
            $('#co-discount').val(0);
            $('#co-results').addClass('hidden').empty();
            $('#co-search').val('');
            renderCart();
        });

        $('#co-submit').click(function() {
            if (cart.size === 0) return;
            const button = $(this).prop('disabled', true);

            const rawDiscount = $('#co-discount').val() ? parseInt($('#co-discount').val().replace(/\D/g, ''), 10) : 0;

            $.ajax({
                url: '{{ route('order.store') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    items: [...cart.entries()].map(([id, item]) => ({
                        variant_id: id,
                        quantity: item.qty
                    })),
                    customer_phone: $('#co-phone').val(),
                    customer_name: $('#co-name').val(),
                    payment_method: $('#co-payment').val(),
                    discount: rawDiscount,
                    note: $('#co-note').val()
                },
                success: function(res) {
                    window.showToast(res.success, 'success');
                    if (confirm('Lập đơn thành công!\n\nBạn có muốn in phiếu bán lẻ cho khách ngay bây giờ không?')) {
                        window.open(res.print_url, '_blank');
                    }
                    window.location.reload();
                },
                error: function(xhr) {
                    window.showAjaxError(xhr);
                    button.prop('disabled', false);
                }
            });
        });

        renderCart();
    });
</script>
