@foreach ($locations as $item)
    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
        <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
            <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $item->code }}</div>
        </td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->zone }}</td>
        <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ $item->shelf }}</td>
        <td
            class="max-w-sm p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
            {{ $item->level }}</td>
        @if ($item->product)
            <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
                <img class="w-10 h-10 rounded-lg" @php $pinImage = $item->product->productImage->first(); @endphp
                    @if ($pinImage) src="{{ Storage::url($pinImage->path) }}"
                    @else src="{{ asset('images/no-photo.svg') }}" @endif
                    alt="{{ $item->product_name }}">
                <div class="text-sm font-normal text-gray-500 dark:text-gray-400">
                    <div class="text-base font-semibold text-gray-9000 dark:text-white">
                        {{ $item->product->product_name }}</div>
                    <div class="text-xs font-normal text-gray-500 dark:text-gray-400">
                        ID: SP{{ $item->product_id }}</div>
                </div>
            </td>
        @else
            <td
                class="max-w-sm p-4 overflow-hidden text-base font-normal text-red-500 truncate xl:max-w-xs dark:text-red-500">
                Chưa có sản phẩm</td>
        @endif
    </tr>
@endforeach
