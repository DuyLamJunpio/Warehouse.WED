@foreach ($products as $item)
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="w-4 p-4">
            <div class="flex items-center">
                <input id="checkbox-1" aria-describedby="checkbox-1" type="checkbox"
                    class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                <label for="checkbox-1" class="sr-only">checkbox</label>
            </div>
        </td>
        <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
            <img class="w-10 h-10 rounded-lg"
                @php $pinImage = $item->productImage->where('is_pined', 1)->first(); @endphp
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
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->expiries_sum_quantity_exp ?? 0 }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->supplier ? $item->supplier->supplier_name : 'Không có nhà cung cấp' }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->category->name }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $item->unit }}</td>
        <td class="p-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white">
            @if ($item->status == 1)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"></div> Còn hàng
                </div>
            @elseif($item->status == 2)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#E3A008"></div> Hết hàng
                </div>
            @else
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"></div> Ngưng nhập/xuất
                </div>
            @endif
        </td>
        <td class="p-4 space-x-2 whitespace-nowrap">
            <button type="button" data-drawer-target="drawer-update-product-default"
                data-drawer-show="drawer-update-product-default" aria-controls="drawer-update-product-default"
                data-drawer-placement="right" data-id-product="{{ $item->id }}"
                class="editProductButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z">
                    </path>
                    <path fill-rule="evenodd"
                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                        clip-rule="evenodd"></path>
                </svg>
                Update
            </button>
            <button type="button" data-drawer-target="drawer-delete-product-default"
                data-drawer-show="drawer-delete-product-default" aria-controls="drawer-delete-product-default"
                data-drawer-placement="right" data-id-product="{{ $item->id }}"
                data-name-product="{{ $item->product_name }}"
                class="deleteSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                Delete item
            </button>
        </td>
    </tr>
@endforeach
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>
<script>
    $(document).ready(function() {

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

        var price = $('.sell_price').text();
        $('.sell_price').text(formatPrice(price));

        var price = $('.import_price').text();
        $('.import_price').text(formatPrice(price));
    });
</script>
