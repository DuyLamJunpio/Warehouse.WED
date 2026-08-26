@forelse ($supplier as $item)
<tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
    {{-- Checkbox --}}
    <td class="w-4 p-4">
        <input type="checkbox" value="{{ $item->id }}"
            class="checkitem rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
    </td>

    {{-- Supplier Info --}}
    <td class="p-4 whitespace-nowrap">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400 shrink-0">
                {{ mb_substr($item->supplier_name, 0, 2, 'UTF-8') }}
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $item->supplier_name }}</div>
                @if($item->tax)
                    <div class="text-[11px] font-mono text-slate-400">MST: {{ $item->tax }}</div>
                @endif
            </div>
        </div>
    </td>

    {{-- Phone --}}
    <td class="p-4 whitespace-nowrap text-xs font-medium text-slate-600 dark:text-slate-300">
        <a href="tel:{{ $item->supplier_phone }}" class="hover:text-indigo-600 inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ $item->supplier_phone }}
        </a>
    </td>

    {{-- Address --}}
    <td class="p-4 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $item->address }}">
        {{ $item->address ?: '—' }}
    </td>

    {{-- Total Amount --}}
    <td class="p-4 whitespace-nowrap text-xs font-bold text-slate-900 dark:text-white">
        {{ number_format((float)$item->total_amount, 0, ',', '.') }} đ
    </td>

    {{-- Status --}}
    <td class="p-4 whitespace-nowrap">
        <x-badge :variant="$item->status == 1 ? 'success' : 'danger'" size="xs">
            {{ $item->status == 1 ? 'Hợp tác' : 'Tạm dừng' }}
        </x-badge>
    </td>

    {{-- Actions --}}
    <td class="p-4 whitespace-nowrap text-right">
        <div class="inline-flex items-center gap-1.5">
            <button type="button"
                data-id-supplier="{{ $item->id }}"
                class="editSupplierButton p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                title="Chỉnh sửa nhà cung cấp">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button"
                data-id-supplier="{{ $item->id }}"
                data-name-supplier="{{ $item->supplier_name }}"
                class="deleteSupplierButton p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                title="Xóa nhà cung cấp">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="p-8 text-center text-slate-400 text-sm">
        Chưa có nhà cung cấp nào.
    </td>
</tr>
@endforelse

