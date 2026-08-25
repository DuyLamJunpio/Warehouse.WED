{{--
    Điều kiện của một quy tắc, viết ra cho người đọc.

    Chỉ đọc — giao diện không cho sửa phần này. Ngữ pháp điều kiện là ĐÓNG:
    thêm một loại điều kiện mới là việc của lập trình viên và phải sửa cả bản
    TypeScript bên web bán hàng, nếu không hai bên tính lệch nhau.
--}}
@php
    $parts = [];
    $techniqueName = fn ($id) => optional($techniques->firstWhere('id', $id))->name ?? ('#' . $id);

    if (!empty($when['technique_ids'])) {
        $parts[] = 'kỹ thuật ∈ <b class="text-indigo-600 dark:text-indigo-400">'
            . e(collect($when['technique_ids'])->map($techniqueName)->implode(', ')) . '</b>';
    }
    if (!empty($when['zone_keys'])) {
        $parts[] = 'vùng ∈ <b class="text-indigo-600 dark:text-indigo-400">'
            . e(implode(', ', $when['zone_keys'])) . '</b>';
    }
    if (!empty($when['tier_ids'])) {
        $parts[] = 'bậc khổ ∈ <b class="text-indigo-600 dark:text-indigo-400">'
            . e(implode(', ', $when['tier_ids'])) . '</b>';
    }
    if (!empty($when['tone'])) {
        $parts[] = 'tông áo = <b class="text-indigo-600 dark:text-indigo-400">'
            . ($when['tone'] === 'dark' ? 'tối' : 'sáng') . '</b>';
    }
    if (isset($when['qty_from'])) {
        $parts[] = 'số lượng từ <b class="text-indigo-600 dark:text-indigo-400">' . (int) $when['qty_from'] . '</b>';
    }
    if (isset($when['qty_to'])) {
        $parts[] = 'số lượng đến <b class="text-indigo-600 dark:text-indigo-400">' . (int) $when['qty_to'] . '</b>';
    }
    if (isset($when['ink_colors_from'])) {
        $parts[] = 'số màu mực từ <b class="text-indigo-600 dark:text-indigo-400">' . (int) $when['ink_colors_from'] . '</b>';
    }
@endphp

@if ($parts)
    KHI {!! implode(' · ', $parts) !!}
@else
    KHI <b class="text-indigo-600 dark:text-indigo-400">mọi đơn</b>
@endif
