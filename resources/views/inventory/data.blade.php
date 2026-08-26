@forelse ($variants as $v)
    @php
        $isOut = $v->quantity <= 0;
        $isLow = !$isOut && $v->quantity <= $threshold;
        $stockBadgeVariant = $isOut ? 'danger' : ($isLow ? 'warning' : 'success');
        $stockBadgeLabel = $isOut ? 'Hết hàng' : ($isLow ? 'Sắp hết' : 'Còn hàng');
    @endphp
    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
        {{-- Product Info --}}
        <td class="p-4">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">
                {{ $v->product->product_name ?? 'Sản phẩm đã xóa' }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $v->product->category->name ?? 'Chưa phân loại' }}
            </div>
        </td>

        {{-- Variant (Size / Color) --}}
        <td class="p-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                {{ $v->label }}
            </span>
        </td>

        {{-- SKU --}}
        <td class="p-4 text-xs font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
            {{ $v->sku ?? '—' }}
        </td>

        {{-- Quantity --}}
        <td class="p-4 whitespace-nowrap">
            <span class="text-sm font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }}">
                {{ $v->quantity }}
            </span>
        </td>

        {{-- Status Badge --}}
        <td class="p-4 whitespace-nowrap">
            <x-badge :variant="$stockBadgeVariant" size="xs">
                {{ $stockBadgeLabel }}
            </x-badge>
        </td>

        {{-- Stock Value --}}
        <td class="p-4 whitespace-nowrap text-xs font-medium text-slate-700 dark:text-slate-300">
            {{ number_format($v->quantity * ($v->product->import_price ?? 0), 0, ',', '.') }} ₫
        </td>

        {{-- Actions --}}
        <td class="p-4 whitespace-nowrap text-right">
            <div class="inline-flex items-center gap-1.5">
                <button type="button" data-variant-id="{{ $v->id }}" data-quantity="{{ $v->quantity }}"
                    data-label="{{ ($v->product->product_name ?? '') . ' — ' . $v->label }}"
                    class="adjustStockButton inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Kiểm kho
                </button>
                <button type="button" data-variant-id="{{ $v->id }}"
                    data-label="{{ ($v->product->product_name ?? '') . ' — ' . $v->label }}"
                    class="historyStockButton p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Xem lịch sử điều chỉnh">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-0">
            <x-empty-state icon="box" title="Không có biến thể tồn kho" description="Không tìm thấy mặt hàng nào khớp bộ lọc." />
        </td>
    </tr>
@endforelse
