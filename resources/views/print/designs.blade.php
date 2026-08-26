<x-app-layout>
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                </li>
                <li>
                    <span class="mx-1 text-slate-400">/</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Duyệt thiết kế</span>
                </li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Duyệt thiết kế</h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
            Đơn in <b>không được</b> nhảy thẳng từ đã thanh toán sang đang in. File có thể không đủ nét,
            nền không trong suốt, hoặc nội dung vi phạm bản quyền. Bắt lỗi ở đây rẻ hơn in hỏng 50 áo.
        </p>
    </div>

    @include('print.partials.tabs')

    {{-- Lọc theo trạng thái --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($labels as $key => $label)
            <a href="{{ route('print.designs', ['status' => $key]) }}"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-full text-sm font-semibold border transition-colors {{ $status === $key
                    ? 'bg-slate-800 text-white border-slate-800 dark:bg-slate-200 dark:text-slate-900 dark:border-slate-200'
                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:border-indigo-400' }}">
                {{ $label }}
                <span class="tabular-nums opacity-70">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200/80 dark:border-slate-700/80">
                        <th class="px-5 py-3">Mã</th>
                        <th class="px-5 py-3">Mẫu áo</th>
                        <th class="px-5 py-3">Kỹ thuật</th>
                        <th class="px-5 py-3 text-right">SL</th>
                        <th class="px-5 py-3 text-right">Thành tiền</th>
                        <th class="px-5 py-3">Đặt lúc</th>
                        <th class="px-5 py-3">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($designs as $design)
                        <tr class="border-b border-slate-100 dark:border-slate-700/60 last:border-0 hover:bg-slate-50/60 dark:hover:bg-slate-700/20">
                            <td class="px-5 py-3">
                                <a href="{{ route('print.designs.show', $design) }}"
                                    class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">{{ $design->code }}</a>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-slate-800 dark:text-slate-100">{{ $design->blank?->name ?? '—' }}</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $design->color_name }} · size {{ $design->size }} ·
                                    {{ count($design->placements ?? []) }} hình
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $design->technique?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">{{ $design->qty }}</td>
                            <td class="px-5 py-3 text-right font-mono tabular-nums font-semibold text-slate-800 dark:text-slate-100">
                                {{ number_format($design->total_price, 0, ',', '.') }} ₫
                            </td>
                            <td class="px-5 py-3 tabular-nums text-slate-500 dark:text-slate-400">
                                {{ $design->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3">
                                @include('print.partials.design-status', ['design' => $design])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Không có thiết kế nào ở trạng thái này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($designs->hasPages())
            <div class="px-5 py-4 border-t border-slate-200/80 dark:border-slate-700/80">
                {{ $designs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
