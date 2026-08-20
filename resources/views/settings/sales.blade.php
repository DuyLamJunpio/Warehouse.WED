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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Cài đặt bán hàng</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Cài đặt thanh toán & vận chuyển
                </h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Cấu hình các phương thức thanh toán và quy tắc tính phí giao hàng hiển thị cho khách trên website bán lẻ.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="submit" form="formSales"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Lưu cài đặt</span>
                </button>
            </div>
        </div>
    </div>

    <form id="formSales" class="space-y-6 max-w-5xl">
        @csrf

        @foreach ($methods as $key => $label)
            @php($config = $sales[$key])
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden" data-method="{{ $key }}">
                {{-- Header of Method Card --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                            @if($key === 'bank_transfer')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $label }}</h2>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $key === 'bank_transfer' ? 'Chuyển khoản QR code / Ngân hàng nội địa' : 'Nhận hàng kiểm tra xong mới trả tiền mặt' }}
                            </span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sales[{{ $key }}][enabled]" value="1" @checked($config['enabled']) class="sr-only peer phuong-thuc-switch">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                        <span class="ml-3 text-xs font-semibold text-slate-700 dark:text-slate-300">Bật hình thức này</span>
                    </label>
                </div>

                {{-- Body configuration --}}
                <div class="p-6 space-y-5 method-content-body">
                    {{-- Free shipping checkbox --}}
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 border border-slate-200/60 dark:border-slate-700/60 flex items-start gap-3">
                        <input type="checkbox" name="sales[{{ $key }}][free_shipping]" value="1" id="free_ship_{{ $key }}"
                            @checked($config['free_shipping']) class="mien-phi mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                        <div>
                            <label for="free_ship_{{ $key }}" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                Miễn phí giao hàng toàn bộ đơn
                            </label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Áp dụng 0 đ phí ship cho mọi đơn hàng sử dụng hình thức thanh toán này.
                            </p>
                        </div>
                    </div>

                    {{-- Dynamic fee configuration --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 khoi-phi transition-opacity">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Phí vận chuyển mặc định (₫)
                            </label>
                            <div class="relative">
                                <input type="text" inputmode="numeric" name="sales[{{ $key }}][shipping_fee]"
                                    value="{{ number_format($config['shipping_fee'], 0, ',', '.') }}" placeholder="0"
                                    class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 pointer-events-none">đ</span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400">Khoản phí giao hàng áp dụng thông thường.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Bên chi trả phí vận chuyển
                            </label>
                            <select name="sales[{{ $key }}][fee_payer]"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option value="customer" @selected($config['fee_payer'] === 'customer')>Khách hàng trả</option>
                                <option value="shop" @selected($config['fee_payer'] === 'shop')>Shop trợ giá / Shop trả</option>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Nếu Shop trả, đơn của khách sẽ không cộng thêm phí ship.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Miễn phí ship từ (Số sản phẩm)
                            </label>
                            <div class="relative">
                                <input type="number" min="1" name="sales[{{ $key }}][free_shipping_min_items]"
                                    value="{{ $config['free_shipping_min_items'] }}" placeholder="VD: 2"
                                    class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 pointer-events-none">món</span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400">Để trống nếu không áp dụng chính sách mua nhiều freeship.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Lưu thay đổi cài đặt</span>
            </button>
        </div>
    </form>

    <script>
        $(function() {
            // Money format helper
            $('.o-tien').on('input', function() {
                const cu = $(this).val();
                const moi = window.nhomNghin(cu);
                if (moi !== cu) $(this).val(moi);
            });

            // Toggle Free Shipping state
            const dongBoKhoiPhi = ($the) => {
                const isEnabled = $the.find('.phuong-thuc-switch').is(':checked');
                const mienPhi = $the.find('.mien-phi').is(':checked');

                $the.find('.method-content-body').toggleClass('opacity-50 pointer-events-none', !isEnabled);
                $the.find('.khoi-phi').toggleClass('opacity-40 pointer-events-none', mienPhi || !isEnabled);
            };

            $('[data-method]').each(function() {
                dongBoKhoiPhi($(this));
            });

            $('.phuong-thuc-switch, .mien-phi').on('change', function() {
                dongBoKhoiPhi($(this).closest('[data-method]'));
            });

            $('#formSales').submit(function(e) {
                e.preventDefault();
                window.submitFormWithProgress($(this), '{{ route('settings.sales.save') }}', function(response) {
                    window.showToast(response.success);
                });
            });
        });
    </script>
</x-app-layout>
