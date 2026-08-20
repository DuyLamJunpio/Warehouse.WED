@forelse ($invoices as $item)
    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors">
        <td class="px-4 py-3.5 whitespace-nowrap">
            <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md">
                HD{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
            </span>
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap">
            @php $username = $user->where('id', $item->user_id)->first(); @endphp
            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                {{ $username->name ?? '—' }}
            </div>
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap">
            @php
                if ($item->invoice_type == 0) {
                    $partner_name = $supplier->where('id', $item->supplier_id)->first()->supplier_name ?? '—';
                } else {
                    $partner_name = $customer->where('id', $item->customer_id)->first()->customer_name ?? '—';
                }
            @endphp
            <div class="text-xs font-medium text-slate-700 dark:text-slate-300">
                {{ $partner_name }}
            </div>
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap">
            <span class="text-xs font-bold text-slate-900 dark:text-white">
                {{ number_format((float)$item->total_amount, 0, ',', '.') }} đ
            </span>
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
            {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap">
            @if ($item->invoice_type == 0)
                <x-badge variant="info" size="xs">Nhập hàng</x-badge>
            @elseif($item->invoice_type == 1)
                <x-badge variant="purple" size="xs">Xuất hàng</x-badge>
            @endif
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap">
            @if ($item->pay_status == 0)
                <x-badge variant="warning" size="xs">Chưa thanh toán</x-badge>
            @elseif($item->pay_status == 1)
                <x-badge variant="success" size="xs">Đã thanh toán</x-badge>
            @elseif($item->pay_status == 2)
                <x-badge variant="danger" size="xs">Quá hạn</x-badge>
            @elseif($item->pay_status == 3)
                <x-badge variant="neutral" size="xs">Đã hủy/xóa</x-badge>
            @elseif($item->pay_status == 4)
                <x-badge variant="neutral" size="xs">Hoàn trả</x-badge>
            @endif
        </td>
        <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs">
            <div class="inline-flex items-center gap-1.5">
                <button type="button" data-drawer-target="drawer-update-product-default"
                    data-drawer-show="drawer-update-product-default" aria-controls="drawer-update-product-default"
                    data-drawer-placement="right" data-id-invoice="{{ $item->id }}"
                    class="editInvoiceButton p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700 dark:hover:text-indigo-400 rounded-lg transition-colors"
                    title="Chi tiết & Cập nhật">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button type="button" data-id-invoice="{{ $item->id }}"
                    class="deleteSupplierButton p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                    title="Xóa hóa đơn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="p-8 text-center">
            <x-empty-state title="Không tìm thấy hóa đơn nào" message="Chưa có dữ liệu hóa đơn nhập/xuất kho phù hợp." />
        </td>
    </tr>
@endforelse
