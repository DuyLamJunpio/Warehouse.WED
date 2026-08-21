@forelse ($products as $item)
    @php
        $thumbnail = $item->thumbnail;
        $totalStock = (int) ($item->variants_sum_quantity ?? 0);
        $stockBadgeVariant = !$item->manage_stock ? 'info' : ($totalStock > 10 ? 'success' : ($totalStock > 0 ? 'warning' : 'danger'));
    @endphp
    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
        {{-- Checkbox --}}
        <td class="w-4 p-4">
            <div class="flex items-center">
                <input type="checkbox" value="{{ $item->id }}"
                    class="product-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
            </div>
        </td>

        {{-- Product Info --}}
        <td class="p-4">
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden border border-slate-200/80 dark:border-slate-700">
                    <img class="w-full h-full object-cover"
                        src="{{ $thumbnail ? Storage::url($thumbnail->path) : asset('images/no-photo.svg') }}"
                        alt="{{ $item->product_name }}"
                        loading="lazy">
                    @if ($item->is_featured)
                        <span class="absolute top-1 left-1 p-0.5 rounded bg-amber-500 text-white shadow-xs" title="Sản phẩm nổi bật">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-slate-900 dark:text-white truncate" title="{{ $item->product_name }}">
                        {{ $item->product_name }}
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="truncate max-w-[120px]">{{ $item->category->name ?? 'Chưa phân loại' }}</span>
                        @if ($item->brand)
                            <span>•</span>
                            <span class="truncate max-w-[100px]">{{ $item->brand }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </td>

        {{-- Prices --}}
        <td class="p-4 whitespace-nowrap">
            <div class="text-sm font-bold text-slate-900 dark:text-white">
                @if ($item->discount_price && $item->discount_price < $item->sell_price)
                    <span class="text-rose-600 dark:text-rose-400">{{ number_format($item->discount_price, 0, ',', '.') }} ₫</span>
                    <span class="text-xs text-slate-400 line-through ml-1">{{ number_format($item->sell_price, 0, ',', '.') }} ₫</span>
                @else
                    <span>{{ number_format($item->sell_price, 0, ',', '.') }} ₫</span>
                @endif
            </div>
            @if ($item->import_price)
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                    Vốn: {{ number_format($item->import_price, 0, ',', '.') }} ₫
                </div>
            @endif
        </td>

        {{-- Stock / Variants Count --}}
        <td class="p-4 whitespace-nowrap">
            @if (!$item->manage_stock)
                <x-badge variant="info" size="xs">Không quản lý tồn</x-badge>
            @else
                <x-badge :variant="$stockBadgeVariant" size="xs">
                    {{ $totalStock }} {{ $item->unit ?? 'cái' }}
                </x-badge>
                @if ($item->variants->isNotEmpty())
                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                        {{ $item->variants->count() }} biến thể
                    </div>
                @endif
            @endif
        </td>

        {{-- Supplier --}}
        <td class="p-4 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
            {{ $item->supplier ? $item->supplier->supplier_name : '—' }}
        </td>

        {{-- Status --}}
        <td class="p-4 whitespace-nowrap">
            @if ($item->status == 1)
                <x-badge variant="success" size="xs">Đang bán</x-badge>
            @elseif ($item->status == 2)
                <x-badge variant="warning" size="xs">Hết hàng</x-badge>
            @else
                <x-badge variant="danger" size="xs">Tạm ngưng</x-badge>
            @endif
        </td>

        {{-- Actions --}}
        <td class="p-4 whitespace-nowrap text-right">
            <div class="inline-flex items-center gap-1">
                <button type="button" data-id-product="{{ $item->id }}"
                    class="editProductButton p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-400 dark:hover:text-indigo-400 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Chỉnh sửa sản phẩm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>

                <button type="button" data-id-product="{{ $item->id }}" data-name-product="{{ $item->product_name }}"
                    class="deleteProductButton p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:text-slate-400 dark:hover:text-rose-400 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Xóa sản phẩm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-0">
            <x-empty-state icon="box" title="Không tìm thấy sản phẩm" description="Thử thay đổi từ khóa hoặc bộ lọc tìm kiếm." />
        </td>
    </tr>
@endforelse
