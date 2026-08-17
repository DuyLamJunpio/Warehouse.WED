@forelse ($variants as $v)
    @php
        $isOut = $v->quantity <= 0;
        $isLow = !$isOut && $v->quantity <= $threshold;
    @endphp
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="p-4 text-sm whitespace-nowrap">
            <div class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $v->product->product_name ?? 'Sản phẩm đã xóa' }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ $v->product->category->name ?? 'Chưa có danh mục' }}
            </div>
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $v->label }}</td>
        <td class="p-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $v->sku }}</td>
        <td
            class="p-4 text-base font-bold whitespace-nowrap {{ $isOut ? 'text-red-600' : ($isLow ? 'text-yellow-500' : 'text-gray-900 dark:text-white') }}">
            {{ $v->quantity }}
        </td>
        <td class="p-4 whitespace-nowrap">
            @if ($isOut)
                <span
                    class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded dark:bg-red-900 dark:text-red-300">Hết
                    hàng</span>
            @elseif ($isLow)
                <span
                    class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-300">Sắp
                    hết</span>
            @else
                <span
                    class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded dark:bg-green-900 dark:text-green-300">Còn
                    hàng</span>
            @endif
        </td>
        <td class="p-4 text-base text-gray-900 whitespace-nowrap dark:text-white">
            {{ number_format($v->quantity * ($v->product->import_price ?? 0), 0, ',', '.') }} ₫
        </td>
        <td class="p-4 space-x-2 whitespace-nowrap">
            <button type="button" data-variant-id="{{ $v->id }}" data-quantity="{{ $v->quantity }}"
                data-label="{{ ($v->product->product_name ?? '') . ' — ' . $v->label }}"
                class="adjustStockButton inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Điều chỉnh
            </button>
            <button type="button" data-variant-id="{{ $v->id }}"
                data-label="{{ ($v->product->product_name ?? '') . ' — ' . $v->label }}"
                class="historyStockButton inline-flex items-center px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                Lịch sử
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Không có biến thể nào khớp bộ lọc.
        </td>
    </tr>
@endforelse
