@forelse ($categories as $item)
    @include('categories.row', ['item' => $item, 'isChild' => false])

    {{-- Danh mục con hiển thị ngay dưới danh mục cha, không phân trang riêng. --}}
    @foreach ($item->children as $child)
        @include('categories.row', ['item' => $child, 'isChild' => true])
    @endforeach
@empty
    <tr>
        <td colspan="7" class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Chưa có danh mục nào.
        </td>
    </tr>
@endforelse
