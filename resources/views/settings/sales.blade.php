<x-app-layout>
    <div class="p-4 bg-white border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Cài đặt bán hàng</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Hình thức thanh toán và cách tính phí giao hàng trên web bán hàng. Lưu xong web tự cập nhật.
        </p>
    </div>

    <form id="formSales" class="p-4 space-y-4">
        @csrf

        @foreach ($methods as $key => $label)
            @php($config = $sales[$key])
            <div class="bg-white rounded-lg shadow dark:bg-gray-800" data-method="{{ $key }}">
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $label }}</h2>
                    <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                        <input type="checkbox" name="sales[{{ $key }}][enabled]" value="1" @checked($config['enabled'])
                            class="w-4 h-4 mr-2 border-gray-300 rounded bg-gray-50 focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600">
                        Cho khách chọn hình thức này
                    </label>
                </div>

                <div class="grid grid-cols-6 gap-4 p-4">
                    <div class="col-span-6">
                        <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                            <input type="checkbox" name="sales[{{ $key }}][free_shipping]" value="1"
                                @checked($config['free_shipping']) class="mien-phi w-4 h-4 mr-2 border-gray-300 rounded bg-gray-50 focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600">
                            Miễn phí giao hàng
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bỏ chọn nếu đơn hàng có tính phí giao.</p>
                    </div>

                    {{-- Khối phí chỉ có nghĩa khi không miễn phí; JS làm mờ nó cho khỏi điền nhầm. --}}
                    <div class="col-span-6 grid grid-cols-6 gap-4 khoi-phi">
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PHÍ GIAO HÀNG (₫)</label>
                            <input type="text" inputmode="numeric" name="sales[{{ $key }}][shipping_fee]"
                                value="{{ number_format($config['shipping_fee'], 0, ',', '.') }}" placeholder="0"
                                class="o-tien bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">AI TRẢ PHÍ</label>
                            <select name="sales[{{ $key }}][fee_payer]"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="customer" @selected($config['fee_payer'] === 'customer')>Khách trả</option>
                                <option value="shop" @selected($config['fee_payer'] === 'shop')>Shop trả</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shop trả thì đơn của khách không cộng
                                phí, nhưng đơn vẫn ghi lại khoản shop bỏ ra.</p>
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">MIỄN PHÍ TỪ (SẢN PHẨM)</label>
                            <input type="number" min="1" name="sales[{{ $key }}][free_shipping_min_items]"
                                value="{{ $config['free_shipping_min_items'] }}" placeholder="Bỏ trống nếu không áp dụng"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mua từ ngần này món trở lên thì
                                không tính phí giao.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="submit"
            class="px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
            Lưu cài đặt
        </button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            const nhomNghin = (v) => String(v).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            $('.o-tien').on('input', function() {
                const cu = $(this).val();
                const moi = nhomNghin(cu);
                if (moi !== cu) $(this).val(moi);
            });

            // Miễn phí giao hàng thì mọi ô phí bên dưới không còn ý nghĩa.
            const dongBoKhoiPhi = ($the) => {
                const mienPhi = $the.find('.mien-phi').is(':checked');
                // Chỉ làm mờ, KHÔNG disable: trường bị disable thì trình duyệt không
                // gửi lên, mà máy chủ vẫn cần biết ai trả phí để lưu lại nguyên vẹn.
                $the.find('.khoi-phi').toggleClass('opacity-40 pointer-events-none', mienPhi);
            };

            $('[data-method]').each(function() {
                dongBoKhoiPhi($(this));
            });

            $('.mien-phi').on('change', function() {
                dongBoKhoiPhi($(this).closest('[data-method]'));
            });

            $('#formSales').submit(function(e) {
                e.preventDefault();
                submitFormWithProgress($(this), '{{ route('settings.sales.save') }}', function(response) {
                    showToast(response.success);
                });
            });
        });
    </script>
</x-app-layout>
