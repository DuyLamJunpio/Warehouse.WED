@php $isChild = $isChild ?? false; @endphp
<tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
    {{-- Thumbnail --}}
    <td class="p-4 whitespace-nowrap">
        @if ($item->image)
            <img class="w-10 h-10 rounded-xl object-cover border border-slate-200/80 dark:border-slate-700" src="{{ Storage::url($item->image) }}"
                alt="{{ $item->name }}">
        @else
            <div class="flex items-center justify-center w-10 h-10 text-slate-400 bg-slate-100 rounded-xl dark:bg-slate-700 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
    </td>

    {{-- Category Name & Indentation --}}
    <td class="p-4 whitespace-nowrap">
        <div class="{{ $isChild ? 'pl-6' : '' }}">
            <div class="flex items-center gap-1.5">
                @if ($isChild)
                    <span class="text-slate-400 font-mono text-xs">└─</span>
                @endif
                <span class="{{ $isChild ? 'text-xs font-semibold text-slate-800 dark:text-slate-200' : 'text-sm font-bold text-slate-900 dark:text-white' }}">
                    {{ $item->name }}
                </span>
            </div>
            @if ($item->description)
                <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs truncate mt-0.5 {{ $isChild ? 'pl-5' : '' }}">{{ $item->description }}</div>
            @endif
        </div>
    </td>

    {{-- Slug --}}
    <td class="p-4 text-xs font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
        {{ $item->slug }}
    </td>

    {{-- Product Count --}}
    <td class="p-4 whitespace-nowrap text-center">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
            {{ $item->products_count ?? 0 }} SP
        </span>
    </td>

    {{-- Sort Order & Reorder Buttons --}}
    <td class="p-4 whitespace-nowrap">
        <div class="inline-flex items-center gap-1">
            <span class="text-xs font-mono text-slate-500 dark:text-slate-400 w-5 text-center">{{ $item->sort_order }}</span>
            <div class="flex flex-col">
                <button type="button" data-id-categories="{{ $item->id }}" data-direction="up"
                    class="reorderCategoriesButton p-0.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors"
                    title="Đẩy lên">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" data-id-categories="{{ $item->id }}" data-direction="down"
                    class="reorderCategoriesButton p-0.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors"
                    title="Hạ xuống">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>
    </td>

    {{-- Status --}}
    <td class="p-4 whitespace-nowrap">
        <x-badge :variant="$item->status == 1 ? 'success' : 'danger'" size="xs">
            {{ $item->status == 1 ? 'Đang dùng' : 'Ngưng dùng' }}
        </x-badge>
    </td>

    {{-- Actions --}}
    <td class="p-4 whitespace-nowrap text-right">
        <div class="inline-flex items-center gap-1.5">
            <button type="button"
                data-id-categories="{{ $item->id }}"
                data-name-categories="{{ $item->name }}"
                data-status-categories="{{ $item->status }}"
                data-parent-categories="{{ $item->parent_id }}"
                data-description-categories="{{ $item->description }}"
                class="editCategoriesButton p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                title="Chỉnh sửa danh mục">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button"
                data-id-categories="{{ $item->id }}"
                data-name-categories="{{ $item->name }}"
                class="deleteCategoriesButton p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                title="Xóa danh mục">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </td>
</tr>
