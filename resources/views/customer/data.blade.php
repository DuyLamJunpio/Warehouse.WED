@forelse ($customers as $item)
    @php
        $spent = (int) ($item->total_spent_sum ?? 0);
        $tier =
            $spent >= \App\Models\Customer::TIER_VIP
                ? 'VIP'
                : ($spent >= \App\Models\Customer::TIER_LOYAL
                    ? 'Thân thiết'
                    : 'Khách mới');
        $tierVariant = $tier === 'VIP' ? 'purple' : ($tier === 'Thân thiết' ? 'info' : 'neutral');
    @endphp
    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
        {{-- Avatar & Name --}}
        <td class="p-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold shrink-0 text-sm border border-slate-200/80 dark:border-slate-700">
                    @if (isset($item->avatar))
                        <img src="{{ Storage::url($item->avatar) }}" alt="{{ $item->customer_name }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($item->customer_name, 0, 1) }}
                    @endif
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->customer_name }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $item->customer_email ?? 'Chưa có email' }}</div>
                </div>
            </div>
        </td>

        {{-- Phone --}}
        <td class="p-4 text-xs font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap font-medium">
            {{ $item->customer_phone }}
        </td>

        {{-- Address --}}
        <td class="p-4 text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate">
            {{ $item->full_address ?: 'Chưa có địa chỉ' }}
        </td>

        {{-- Order Count --}}
        <td class="p-4 text-xs font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap text-center">
            {{ $item->order_count ?? 0 }}
        </td>

        {{-- Total Spent --}}
        <td class="p-4 text-xs font-bold text-slate-900 dark:text-white whitespace-nowrap">
            {{ number_format($spent, 0, ',', '.') }} ₫
        </td>

        {{-- Tier Badge --}}
        <td class="p-4 whitespace-nowrap">
            <x-badge :variant="$tierVariant" size="xs">
                {{ $tier }}
            </x-badge>
        </td>

        {{-- Actions --}}
        <td class="p-4 whitespace-nowrap text-right">
            <div class="inline-flex items-center gap-1.5">
                <button type="button" data-id-customer="{{ $item->id }}"
                    class="viewCustomerButton inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Hồ sơ
                </button>
                <button type="button"
                    data-id-customer="{{ $item->id }}"
                    data-item-customer="{{ json_encode($item) }}"
                    class="editCustomerButton p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Chỉnh sửa thông tin">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button type="button"
                    data-id-customer="{{ $item->id }}"
                    data-name-customer="{{ $item->customer_name }}"
                    class="deleteCustomerButton p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                    title="Xóa khách hàng">
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
            <x-empty-state icon="orders" title="Không tìm thấy khách hàng" description="Chưa có thông tin khách hàng nào khớp với tìm kiếm." />
        </td>
    </tr>
@endforelse
