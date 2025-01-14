@foreach ($supplier as $item)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
    <td class="w-4 p-4">
        <div class="flex items-center">
            <input id="checkbox-194556" aria-describedby="checkbox-1" type="checkbox"
                class="checkitem w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
            <label for="checkbox-194556" class="sr-only">checkbox</label>
        </div>
    </td>
    <td
        class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
        <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $item->supplier_name }}</div>
    </td>
    <td
        class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
        {{ $item->supplier_phone }}</td>
    <td
        class="max-w-sm p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
        {{ $item->address }}</td>
    <td
        class="max-w-sm p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
        {{ $item->tax }}</td>
    <td
        class="max-w-sm p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
        {{ $item->total_amount }}</td>
    <td
        class="p-4 text-base font-medium {{ $item->status == 1 ? 'text-green-500' : 'text-red-600' }} whitespace-nowrap">
        {{ $item->status == 1 ? 'Đang hợp tác' : 'Đã huỷ' }}</td>
    <td class="p-4 space-x-2 whitespace-nowrap">
        <button type="button"
            data-drawer-target="drawer-update-product-default"
            data-drawer-show="drawer-update-product-default"
            aria-controls="drawer-update-product-default"
            data-drawer-placement="right"
            data-id-supplier="{{ $item->id }}"
            class="editSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z">
                </path>
                <path fill-rule="evenodd"
                    d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                    clip-rule="evenodd"></path>
            </svg>
            Update
        </button>
        <button type="button"
            data-drawer-target="drawer-delete-product-default"
            data-drawer-show="drawer-delete-product-default"
            aria-controls="drawer-delete-product-default"
            data-drawer-placement="right"
            data-id-supplier="{{ $item->id }}"
            data-name-supplier="{{ $item->supplier_name }}"
            class="deleteSupplierButton inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            Delete item
        </button>
    </td>
</tr>
@endforeach
