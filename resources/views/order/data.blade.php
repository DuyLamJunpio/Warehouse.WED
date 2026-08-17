@php
    $badge = [
        'pending' => 'text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-300',
        'confirmed' => 'text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-300',
        'packing' => 'text-indigo-800 bg-indigo-100 dark:bg-indigo-900 dark:text-indigo-300',
        'shipping' => 'text-yellow-800 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300',
        'completed' => 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-300',
        'cancelled' => 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-300',
        'returned' => 'text-orange-800 bg-orange-100 dark:bg-orange-900 dark:text-orange-300',
    ];
@endphp

@forelse ($orders as $order)
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="p-4 whitespace-nowrap">
            <div class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $order->order_code ?? '#' . $order->id }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</div>
        </td>
        <td class="p-4 text-sm whitespace-nowrap">
            <div class="font-medium text-gray-900 dark:text-white">
                {{ $order->shipping_name ?? ($order->customer->customer_name ?? 'Khách lẻ') }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->shipping_phone ?? '—' }}</div>
        </td>
        <td class="p-4 text-base text-gray-900 whitespace-nowrap dark:text-white">
            {{ $order->productInvoices->sum('quantity') }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ number_format($order->total_amount, 0, ',', '.') }} ₫
        </td>
        <td class="p-4 whitespace-nowrap">
            <span
                class="px-2 py-1 text-xs font-medium rounded {{ $badge[$order->order_status] ?? 'text-gray-800 bg-gray-100' }}">
                {{ $order->order_status_label ?? 'Chưa có trạng thái' }}
            </span>
        </td>
        <td class="p-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
            {{ $order->payment_method ?? '—' }}
        </td>
        <td class="p-4 space-x-2 whitespace-nowrap">
            <button type="button" data-order-id="{{ $order->id }}"
                class="viewOrderButton inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Chi tiết
            </button>
            <a href="/order/{{ $order->id }}/print" target="_blank"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                In phiếu
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Chưa có đơn hàng nào khớp bộ lọc.
        </td>
    </tr>
@endforelse
