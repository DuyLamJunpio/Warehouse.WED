@php
    $nutLui = [
        \App\Models\Invoice::STATUS_CANCELLED => 'text-rose-700 bg-rose-50 border-rose-200 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
        \App\Models\Invoice::STATUS_RETURNED => 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    ];

    $badgeVariant = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'packing' => 'purple',
        'shipping' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'returned' => 'neutral',
    ];
@endphp

@forelse ($orders as $order)
    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
        {{-- Order Code & Time --}}
        <td class="p-4 whitespace-nowrap">
            <div class="font-bold text-slate-900 dark:text-white text-sm">
                {{ $order->order_code ?? '#' . $order->id }}
            </div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ $order->created_at->format('d/m/Y H:i') }}
            </div>
        </td>

        {{-- Customer Info --}}
        <td class="p-4 whitespace-nowrap">
            <div class="font-semibold text-slate-900 dark:text-white text-sm">
                {{ $order->shipping_name ?? ($order->customer->customer_name ?? 'Khách lẻ') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $order->shipping_phone ?? '—' }}
            </div>
        </td>

        {{-- Items Count --}}
        <td class="p-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
            <span class="font-semibold">{{ $order->productInvoices->sum('quantity') }}</span> món
        </td>

        {{-- Total Amount --}}
        <td class="p-4 whitespace-nowrap">
            <div class="text-sm font-bold text-slate-900 dark:text-white">
                {{ number_format($order->total_amount, 0, ',', '.') }} ₫
            </div>
            @if ($order->discount > 0)
                <div class="text-[11px] text-rose-500">Giảm: -{{ number_format($order->discount, 0, ',', '.') }} ₫</div>
            @endif
        </td>

        {{-- Status --}}
        <td class="p-4 whitespace-nowrap">
            <x-badge :variant="$badgeVariant[$order->order_status] ?? 'neutral'" size="xs">
                {{ $order->order_status_label ?? 'Chưa có trạng thái' }}
            </x-badge>
        </td>

        {{-- Payment Method --}}
        <td class="p-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
            <span class="inline-flex items-center gap-1">
                @if ($order->payment_method == 'cash')
                    <span>💵 Tiền mặt</span>
                @elseif ($order->payment_method == 'bank_transfer')
                    <span>🏦 Chuyển khoản</span>
                @else
                    <span>💳 {{ $order->payment_method ?? '—' }}</span>
                @endif
            </span>
        </td>

        {{-- Actions --}}
        <td class="p-4 whitespace-nowrap text-right">
            <div class="inline-flex items-center gap-1.5">
                {{-- Fast Status Change Buttons --}}
                @foreach ($order->next_statuses as $next)
                    <button type="button" data-order-id="{{ $order->id }}" data-status="{{ $next }}"
                        class="btn-doi-trang-thai inline-flex items-center px-2.5 py-1.5 text-xs font-semibold rounded-lg border transition-all shadow-xs {{ $nutLui[$next] ?? 'text-indigo-700 bg-indigo-50 border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800' }}">
                        {{ \App\Models\Invoice::ORDER_STATUSES[$next] }}
                    </button>
                @endforeach

                <button type="button" data-order-id="{{ $order->id }}"
                    class="viewOrderButton p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-400 dark:hover:text-indigo-400 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Chi tiết đơn hàng">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                <a href="/order/{{ $order->id }}/print" target="_blank"
                    class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="In phiếu bán lẻ">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-0">
            <x-empty-state icon="orders" title="Không có đơn hàng nào" description="Chưa có đơn hàng nào khớp với điều kiện tìm kiếm." />
        </td>
    </tr>
@endforelse
