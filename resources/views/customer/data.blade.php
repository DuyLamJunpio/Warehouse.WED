@php
    $tierBadge = [
        'VIP' => 'text-purple-800 bg-purple-100 dark:bg-purple-900 dark:text-purple-300',
        'Thân thiết' => 'text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-300',
        'Khách mới' => 'text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-300',
    ];
@endphp

@forelse ($customers as $item)
    @php
        $spent = (int) ($item->total_spent_sum ?? 0);
        $tier =
            $spent >= \App\Models\Customer::TIER_VIP
                ? 'VIP'
                : ($spent >= \App\Models\Customer::TIER_LOYAL
                    ? 'Thân thiết'
                    : 'Khách mới');
    @endphp
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="flex items-center p-4 space-x-4 whitespace-nowrap">
            <img class="w-10 h-10 rounded-lg"
                @if (isset($item->avatar)) src="{{ Storage::url($item->avatar) }}"
            @else
            src="https://static.vecteezy.com/system/resources/previews/004/141/669/original/no-photo-or-blank-image-icon-loading-images-or-missing-image-mark-image-not-available-or-image-coming-soon-sign-simple-nature-silhouette-in-frame-isolated-illustration-vector.jpg" @endif
                alt="{{ $item->customer_name }}">
            <div>
                <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $item->customer_name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->customer_email ?? 'Chưa có email' }}
                </div>
            </div>
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->customer_phone }}</td>
        <td class="max-w-xs p-4 overflow-hidden text-sm text-gray-500 truncate dark:text-gray-400">
            {{ $item->full_address }}</td>
        <td class="p-4 text-base text-gray-900 whitespace-nowrap dark:text-white">{{ $item->order_count ?? 0 }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ number_format($spent, 0, ',', '.') }} ₫</td>
        <td class="p-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs font-medium rounded {{ $tierBadge[$tier] }}">{{ $tier }}</span>
        </td>
        <td class="p-4 space-x-2 whitespace-nowrap">
            <button type="button" data-id-customer="{{ $item->id }}"
                class="viewCustomerButton inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Hồ sơ
            </button>
            <button type="button" data-drawer-target="drawer-update-product-default"
                data-drawer-show="drawer-update-product-default" aria-controls="drawer-update-product-default"
                data-drawer-placement="right" data-id-customer="{{ $item->id }}"
                data-item-customer="{{ json_encode($item) }}"
                class="editSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                Sửa
            </button>
            <button type="button" data-drawer-target="drawer-delete-product-default"
                data-drawer-show="drawer-delete-product-default" aria-controls="drawer-delete-product-default"
                data-drawer-placement="right" data-id-customer="{{ $item->id }}"
                data-name-customer="{{ $item->customer_name }}"
                class="deleteSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                Xóa
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Chưa có khách hàng nào khớp bộ lọc.
        </td>
    </tr>
@endforelse
