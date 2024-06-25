@foreach ($invoices as $item)
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">HD{{ $item->id }}
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            @php $username = $user->where('id', $item->user_id)->first(); @endphp
            {{ $username->name }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            @php
                if ($item->invoice_type == 0) {
                    $partner_name = $supplier->where('id', $item->supplier_id)->first()->supplier_name;
                } else {
                    $partner_name = $customer->where('id', $item->customer_id)->first()->customer_name;
                }
            @endphp
            {{ $partner_name }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            @php
                $formattedNumber = number_format($item->total_amount, 0, ',', '.') . ' đ'; // Định dạng số và thêm ký hiệu tiền "đ"
            @endphp
            {{ $formattedNumber }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $item->created_at }}
        </td>
        <td class="p-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white">
            @if ($item->invoice_type == 0)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"></div> Nhập hàng
                </div>
            @elseif($item->invoice_type == 1)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#1008ff"></div> Xuất hàng
                </div>
            @endif
        </td>
        <td class="p-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white">
            @if ($item->pay_status == 0)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#ff7300"></div> Chưa thanh toán
                </div>
            @elseif($item->pay_status == 1)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#00ff04"></div> Đã thanh toán
                </div>
            @elseif($item->pay_status == 2)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#f6ff00"></div> Quá hạn
                </div>
            @elseif($item->pay_status == 3)
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full mr-2" style="background-color:#ff0000"></div> Đã xóa
                </div>
            @endif
        </td>
        <td class="p-4 space-x-2 whitespace-nowrap">
            <button type="button" data-drawer-target="drawer-update-product-default"
                data-drawer-show="drawer-update-product-default" aria-controls="drawer-update-product-default"
                data-drawer-placement="right" data-id-invoice="{{ $item->id }}"
                class="editInvoiceButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
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
                data-drawer-placement="right" data-id-invoice="{{ $item->id }}"
                class="deleteSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                Delete
            </button>
        </td>
    </tr>
@endforeach
