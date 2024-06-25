@foreach ($products as $item)
    @php $pinImage = $item->productImage->where('is_pined', 1)->first(); @endphp
    <tr id="{{ $item->id }}" class="checkbox_click hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="w-4 p-4">
            <div class="flex items-center">
                <input id="checkbox-{{ $item->id }}" aria-describedby="checkbox-{{ $item->id }}" type="checkbox"
                    data-product-id="{{ $item->id }}" data-product-name="{{ $item->product_name }}"
                    @if (isset($pinImage)) data-product-image="{{ Storage::url($pinImage->path) }}"
                    @elseif($item->productImage->first())
                    data-product-image="{{ Storage::url($item->productImage->first()->path) }}"
                    @else
                    data-product-image="https://static.vecteezy.com/system/resources/previews/004/141/669/original/no-photo-or-blank-image-icon-loading-images-or-missing-image-mark-image-not-available-or-image-coming-soon-sign-simple-nature-silhouette-in-frame-isolated-illustration-vector.jpg" @endif
                    data-price="{{ $item->sell_price }}" data-cost="{{ $item->import_price }}"
                    data-inventory="{{ $item->expiries_sum_quantity_exp ?? 0 }}" data-status="{{ $item->status }}"
                    class="checkitem w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                <label hidden for="checkbox-{{ $item->id }}" class="sr-only">checkbox</label>
            </div>
        </td>
        <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
            <img class="w-10 h-10 rounded-lg"
                @if (isset($pinImage)) src="{{ Storage::url($pinImage->path) }}"
                @elseif($item->productImage->first())
                src="{{ Storage::url($item->productImage->first()->path) }}"
                @else
                src="https://static.vecteezy.com/system/resources/previews/004/141/669/original/no-photo-or-blank-image-icon-loading-images-or-missing-image-mark-image-not-available-or-image-coming-soon-sign-simple-nature-silhouette-in-frame-isolated-illustration-vector.jpg" @endif
                alt="{{ $item->product_name }}">
            <div class="text-sm font-normal text-gray-500 dark:text-gray-400">
                <div class="text-base font-semibold text-gray-9000 dark:text-white">{{ $item->product_name }}</div>
                <div class="text-xs font-normal text-gray-500 dark:text-gray-400">
                    {{ $item->location ? $item->location->code : 'Không có vị trí' }}</div>
            </div>
        </td>
        <td class="sell_price p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->sell_price }}
        </td>
        <td class="import_price p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->import_price }}</td>
        <td
            class="p-4 text-base font-medium {{ $item->expiries_sum_quantity_exp ? 'text-gray-900' : 'text-red-500' }} whitespace-nowrap {{ $item->expiries_sum_quantity_exp ? 'dark:text-white' : 'dark:text-red-500' }}">
            {{ $item->expiries_sum_quantity_exp ?? 'Hết hàng' }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->supplier->supplier_name }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->category ? $item->category->name : 'Không có danh mục' }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $item->unit }}</td>
    </tr>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        updatePricesAndTotal();

        $('.checkitem').each(function() {
            const productId = $(this).data('product-id');
            const status = $(this).data('status');
            const tr = $(`#${productId}`);
            if (status == 2 && $('#invoice_type').val() == 1) {
                tr.removeClass("checkbox_click");
                tr.css({
                    "opacity": "0.5",
                    "pointer-events": "none"
                });
            }

            if (status == 2 && $('#invoice_type_edit').val() == 1) {
                tr.removeClass("checkbox_click");
                tr.css({
                    "opacity": "0.5",
                    "pointer-events": "none"
                });
            }
        });

        $('.checkitem').click(function(event) {
            event.preventDefault();
        });
        // Khi click vào thẻ tr
        $('.checkbox_click').click(function(event) {
            // Chỉ thực hiện khi click không phải vào checkbox
            if (!$(event.target).is('.checkitem')) {
                var checkbox = $(this).find('.checkitem');
                // Toggle trạng thái của checkbox
                checkbox.prop('checked', !checkbox.prop('checked'));
                // Kích hoạt sự kiện change để xử lý dữ liệu
                checkbox.trigger('change');
            }
        });

        // Bắt sự kiện change trên checkbox
        $(document).on('change', '.checkitem', function() {
            const productId = $(this).data('productId');
            const productName = $(this).data('productName');
            const productImage = $(this).data('productImage');
            const price = $(this).data('price');
            const cost = $(this).data('cost');
            const inventory = $(this).data('inventory');

            if ($(this).is(':checked')) {
                const row = `
                <tr id="${productId}" class="hover:bg-gray-100 dark:hover:bg-gray-700">
    <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
        <img class="w-10 h-10 rounded-lg" src="${productImage}">
        <div class="text-sm font-normal text-gray-500 dark:text-gray-400">
            <div class="text-base font-semibold text-gray-900 dark:text-white">${productName}</div>
        </div>
    </td>
    <td class="cost p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">${formatPrice(cost)}</td>
    <td class="price p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white" hidden>
        ${formatPrice(price)}</td>
    <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
        <div class="flex">
            <input type="number" inputmode="numeric" data-product-id="${productId}"
                class="quantity rounded-none rounded-e-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="0" required min="0" value="1">
            <span
                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                <span style="margin-right: 5px;">Tồn kho:</span><strong class="inventory">${inventory}</strong>
            </span>
        </div>
    </td>
    <td class="total p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">0</td>
    <td class="p-4">
        <button type="button" data-product-id="${productId}"
            class="delete-product-button inline-flex items-center px-2 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                    clip-rule="evenodd">
                </path>
            </svg>
        </button>
    </td>
</tr>
                `;

                const row_nhap = `
                <tr id="${productId}" class="hover:bg-gray-100 dark:hover:bg-gray-700">
    <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
        <img class="w-10 h-10 rounded-lg" src="${productImage}">
        <div class="text-sm font-normal text-gray-500 dark:text-gray-400">
            <div class="text-base font-semibold text-gray-900 dark:text-white">${productName}</div>
        </div>
    </td>
    <td class="cost p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">${formatPrice(cost)}</td>
    <td class="price p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white" hidden>
        ${formatPrice(price)}</td>
    <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
        <div class="flex">
            <input type="number" inputmode="numeric" data-product-id="${productId}"
                class="quantity rounded-none rounded-e-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="0" required min="0" value="1">
            <span
                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                <span style="margin-right: 5px;">Tồn kho:</span><strong class="inventory">${inventory}</strong>
            </span>
        </div>
    </td>
    <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
        <input type="date" name="expiry_date"
        class="expiry bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
    </td>
    <td class="total p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">0</td>
    <td class="p-4">
        <button type="button" data-product-id="${productId}"
            class="delete-product-button inline-flex items-center px-2 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                    clip-rule="evenodd">
                </path>
            </svg>
        </button>
    </td>
</tr>
                `;

                if ($('#invoice_type').val() == 0) {
                    $('#productTableAdd').prepend(row_nhap);
                    $('#productTableEdit').prepend(row_nhap);
                } else {
                    $('#productTableAdd').prepend(row);
                    $('#productTableEdit').prepend(row);
                }

            } else {
                $(`#productTableAdd tr`).each(function() {
                    if ($(this).find('.delete-product-button').data('productId') ===
                        productId) {
                        $(this).remove();
                        updateTotalAmount();
                        updateTotalAmountEdit();
                    }
                });
                $(`#productTableEdit tr`).each(function() {
                    if ($(this).find('.delete-product-button').data('productId') ===
                        productId) {
                        $(this).remove();
                        updateTotalAmount();
                        updateTotalAmountEdit();
                    }
                });
            }
        });

        // Xử lý sự kiện click trên nút xóa
        $(document).on('click', '.delete-product-button', function() {
            const productId = $(this).data('productId');
            $(`.checkitem[data-product-id="${productId}"]`).prop('checked', false);
            $(this).closest('tr').remove();
            updateTotalAmount();
            updateTotalAmountEdit();
        });

        function formatPrice(price) {
            // Chuyển đổi giá trị price thành số, loại bỏ các dấu chấm nếu có
            var numericPrice = parseInt(price.toString().replace(/\./g, '').replace('₫', '').trim());
            // Kiểm tra xem numericPrice có phải là số hợp lệ không
            if (!isNaN(numericPrice)) {
                // Định dạng số theo chuẩn Việt Nam và trả về
                return numericPrice.toLocaleString('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                });
            } else {
                // Trả về chuỗi rỗng hoặc giá trị mặc định nếu price không phải là số
                return '';
            }
        }


        $(document).on('change', '#invoice_type', function() {
            var invoiceType = $(this).val();
            if (invoiceType == 0) {
                $('.price').hide();
                $('.cost').show();
                $('#expiry_th').show();
                $('.expiry').show();
                $('#add_product').attr('colspan', '6');
                $('#productTableAdd tr:not(:last)').remove();
                $('#productTableEdit tr:not(:last)').remove();
            } else {
                $('.price').show();
                $('.cost').hide();
                $('#expiry_th').hide();
                $('.expiry').hide();
                $('#add_product').attr('colspan', '5');
                $('#productTableAdd tr:not(:last)').remove();
                $('#productTableEdit tr:not(:last)').remove();
            }
        });

        function recalculateRowTotal($quantityInput) {
            var quantity = $quantityInput.val();
            if (quantity == '') {
                quantity = 0;
            }
            let valueFind = $('#invoice_type').val() == 1 ? '.price' : '.cost';
            const priceText = $quantityInput.closest('tr').find(valueFind).text();
            const price = parseInt(priceText.replace(/\./g, '').replace('₫', '').trim());
            const total = parseInt(quantity) * price;
            $quantityInput.closest('tr').find('.total').text(formatPrice(total));
        }

        // Khi số lượng thay đổi
        $(document).on('input', '.quantity', function() {
            recalculateRowTotal($(this));

            // Cập nhật tổng tiền sau khi thay đổi số lượng
            updateTotalAmount();
        });

        // Hàm cập nhật tổng tiền
        function updateTotalAmount() {
            let taxableAmount = 0;
            let totalAmount = 0;
            $('.total').each(function() {
                const totalValue = parseInt($(this).text().replace(/\./g, '').replace('₫', '')
                    .trim());
                if (!isNaN(totalValue)) {
                    taxableAmount += totalValue;
                }
            });
            $('#taxable_amount').text(formatPrice(taxableAmount));

            let discount = parseInt($('#discount').val()); // Đảm bảo rằng discount là một số
            if (isNaN(discount)) discount = 0; // Nếu discount không phải là số, đặt mặc định là 0
            // Tính toán totalAmount với discount đã được xác nhận là số
            totalAmount = taxableAmount * (1 - discount / 100) * 1.1;
            $('#total_amount_input').val(parseInt(totalAmount));
            $('#total_amount').text(formatPrice(parseInt(totalAmount)));
        }

        // Hàm cập nhật định dạng giá và tính toán tổng tiền ban đầu
        function updatePricesAndTotal() {
            $('.sell_price, .import_price').each(function() {
                var price = parseInt($(this).text().replace(/\./g, '').replace('₫', '').trim());
                $(this).text(formatPrice(price));
            });
            updateTotalAmount();
            updateTotalAmountEdit();
        }


        $(document).on('input', '#discount', function() {
            if ($(this).val() == '') {
                $('#discountNumber').text("0%");
            } else {
                $('#discountNumber').text($(this).val() + "%");
                updateTotalAmount();
            }
        })

        $(document).on('input', '.quantity', function() {
            var inventory = $(this).closest('tr').find('.inventory').text();
            if ($('#invoice_type').val() == '0') {
                $(this).removeAttr('max');
            } else {
                if (inventory == 1) {
                    $(this).removeAttr('max');
                } else {
                    $(this).attr('max', inventory);
                }
            }
        });


        // edittttttttttttttttttttttttt
        $(document).on('change', '#invoice_type_edit', function() {
            if ($(this).val() == 0) {
                $('.price').hide();
                $('.cost').show();
            } else {
                $('.price').show();
                $('.cost').hide();
            }

            // Tính lại tổng tiền cho mỗi hàng khi loại hóa đơn thay đổi
            $('.quantity').each(function() {
                recalculateRowTotalEdit($(this));
            });
        });

        function recalculateRowTotalEdit($quantityInput) {
            var quantity = $quantityInput.val();
            if (quantity == '') {
                quantity = 0;
            }
            let valueFind = $('#invoice_type_edit').val() == 1 ? '.price' : '.cost';
            const priceText = $quantityInput.closest('tr').find(valueFind).text();
            const price = parseInt(priceText.replace(/\./g, '').replace('₫', '').trim());
            const total = parseInt(quantity) * price;
            $quantityInput.closest('tr').find('.total').text(formatPrice(total));
        }

        $(document).on('input', '.quantity', function() {
            recalculateRowTotalEdit($(this));

            // Cập nhật tổng tiền sau khi thay đổi số lượng
            updateTotalAmountEdit();
        });

        // Hàm cập nhật tổng tiền
        function updateTotalAmountEdit() {
            let taxableAmount = 0;
            let totalAmount = 0;
            $('.total').each(function() {
                const totalValue = parseInt($(this).text().replace(/\./g, '').replace('₫', '')
                    .trim());
                if (!isNaN(totalValue)) {
                    taxableAmount += totalValue;
                }
            });
            $('#taxable_amount_edit').text(formatPrice(taxableAmount));

            let discount = parseInt($('#discount_edit').val()); // Đảm bảo rằng discount là một số
            if (isNaN(discount)) discount = 0; // Nếu discount không phải là số, đặt mặc định là 0
            // Tính toán totalAmount với discount đã được xác nhận là số
            totalAmount = taxableAmount * (1 - discount / 100) * 1.1;
            $('#total_amount_input_edit').val(parseInt(totalAmount));
            $('#total_amount_edit').text(formatPrice(parseInt(totalAmount)));
        }


        $(document).on('input', '#discount_edit', function() {
            if ($(this).val() == '') {
                $('#discountNumberEdit').text("0%");
            } else {
                $('#discountNumberEdit').text($(this).val() + "%");
                updateTotalAmount();
            }
        })

        $(document).on('input', '.quantity', function() {
            var inventory = $(this).closest('tr').find('.inventory').text();
            if ($('#invoice_type_edit').val() == '0') {
                $(this).removeAttr('max');
            } else {
                if (inventory == 1) {
                    $(this).removeAttr('max');
                } else {
                    $(this).attr('max', inventory);
                }
            }
        });
    });
</script>
