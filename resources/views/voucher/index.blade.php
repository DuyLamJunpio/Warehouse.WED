<x-app-layout>
    @php
        $money = fn ($amount) => number_format((int) $amount, 0, ',', '.') . ' ₫';
        $inputClass = 'block w-full rounded-xl border-slate-300 bg-white px-3.5 py-2 text-sm shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white';
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Trang chủ</a></li>
                    <li><span class="mx-1 text-slate-400">/</span><span class="font-medium text-slate-800 dark:text-slate-200">Voucher</span></li>
                </ol>
            </nav>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">Quản lý voucher</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tạo mã giảm giá, mức đơn tối thiểu, giới hạn lượt dùng và thời hạn áp dụng.</p>
        </div>
        <button type="button" id="open-create-voucher" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 active:bg-indigo-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Thêm voucher
        </button>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Tổng voucher</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $counts['all'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-xs dark:border-emerald-900/50 dark:bg-emerald-950/20">
            <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Đang áp dụng</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $counts['active'] }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 shadow-xs dark:border-indigo-900/50 dark:bg-indigo-950/20">
            <p class="text-xs font-medium text-indigo-700 dark:text-indigo-300">Loại voucher hỗ trợ</p>
            <p class="mt-1 text-sm font-semibold text-indigo-800 dark:text-indigo-200">% · Giảm tiền · Freeship</p>
        </div>
    </div>

    <form method="GET" class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo mã hoặc tên voucher..." class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <select name="filter" class="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="all" @selected($filter === 'all')>Tất cả trạng thái</option>
                <option value="active" @selected($filter === 'active')>Đang áp dụng</option>
                <option value="inactive" @selected($filter === 'inactive')>Đã tắt</option>
                <option value="expired" @selected($filter === 'expired')>Đã hết hạn</option>
            </select>
            <button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">Lọc</button>
            @if(request()->filled('keyword') || $filter !== 'all')
                <a href="{{ route('vouchers.index') }}" class="px-2 py-2 text-sm font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">Xóa lọc</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:border-slate-700/80 dark:bg-slate-800/75 dark:text-slate-400">
                    <tr><th class="p-4">Mã / tên</th><th class="p-4">Ưu đãi</th><th class="p-4">Điều kiện</th><th class="p-4">Thời hạn</th><th class="p-4 text-center">Đã dùng</th><th class="p-4">Trạng thái</th><th class="p-4 text-right">Thao tác</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @forelse($vouchers as $voucher)
                        @php
                            $isExpired = $voucher->ends_at?->isPast();
                            $isScheduled = $voucher->starts_at?->isFuture();
                            $isExhausted = $voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit;
                            $benefit = match ($voucher->type) {
                                \App\Models\Voucher::TYPE_PERCENTAGE => 'Giảm ' . $voucher->value . '%' . ($voucher->max_discount ? ', tối đa ' . $money($voucher->max_discount) : ''),
                                \App\Models\Voucher::TYPE_FIXED_AMOUNT => 'Giảm ' . $money($voucher->value),
                                default => 'Miễn phí vận chuyển',
                            };
                            $statusText = ! $voucher->status ? 'Đã tắt' : ($isExpired ? 'Hết hạn' : ($isScheduled ? 'Sắp diễn ra' : ($isExhausted ? 'Hết lượt' : 'Đang áp dụng')));
                            $statusClass = $voucher->is_currently_available ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                            <td class="p-4">
                                <code class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold tracking-wide text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $voucher->code }}</code>
                                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $voucher->name }}</p>
                                @if($voucher->description)<p class="mt-0.5 max-w-xs truncate text-xs text-slate-500 dark:text-slate-400">{{ $voucher->description }}</p>@endif
                            </td>
                            <td class="p-4 whitespace-nowrap"><p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $benefit }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $voucher->type_label }}</p></td>
                            <td class="p-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300"><p>Đơn từ {{ $money($voucher->min_order_amount) }}</p><p class="mt-1">{{ $voucher->usage_limit ? 'Tối đa ' . number_format($voucher->usage_limit) . ' lượt' : 'Không giới hạn lượt' }}</p></td>
                            <td class="p-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300"><p>{{ $voucher->starts_at ? 'Từ ' . $voucher->starts_at->format('d/m/Y H:i') : 'Bắt đầu ngay' }}</p><p class="mt-1">{{ $voucher->ends_at ? 'Đến ' . $voucher->ends_at->format('d/m/Y H:i') : 'Không thời hạn' }}</p></td>
                            <td class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-200">{{ number_format($voucher->used_count) }}@if($voucher->usage_limit !== null)<span class="text-slate-400">/{{ number_format($voucher->usage_limit) }}</span>@endif</td>
                            <td class="p-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td class="p-4 text-right"><div class="inline-flex items-center gap-1">
                                <button type="button" data-voucher='@json($voucher)' class="edit-voucher rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-700 dark:hover:text-white" title="Chỉnh sửa"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                <form method="POST" action="{{ route('vouchers.toggle', $voucher) }}" class="inline">@csrf<button title="{{ $voucher->status ? 'Tắt voucher' : 'Bật voucher' }}" class="rounded-lg p-2 {{ $voucher->status ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M5.07 19h13.86a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16a2 2 0 001.73 3z" /></svg></button></form>
                                @if($voucher->status)<form method="POST" action="{{ route('vouchers.deactivate', $voucher) }}" class="inline" onsubmit="return confirm('Ngừng áp dụng mã {{ $voucher->code }}? Voucher sẽ được giữ lại, không bị xóa.');">@csrf<button title="Ngừng áp dụng (giữ lại dữ liệu)" class="rounded-lg p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728" /></svg></button></form>@endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="orders" title="Chưa có voucher" description="Tạo voucher đầu tiên để sẵn sàng chạy chương trình khuyến mãi." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200/80 px-4 dark:border-slate-700/80">{{ $vouchers->links('vendor.pagination.tailwind') }}</div>
    </div>

    <div id="drawer-create-voucher" class="fixed right-0 top-0 z-40 flex h-screen w-full translate-x-full flex-col overflow-y-auto bg-white shadow-2xl transition-transform sm:max-w-lg dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700"><div><h2 class="font-bold text-slate-900 dark:text-white">Tạo voucher mới</h2><p class="mt-0.5 text-xs text-slate-500">Mã sẽ được chuyển sang chữ in hoa.</p></div><button type="button" class="close-voucher-drawer rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">✕</button></div>
        <form method="POST" action="{{ route('vouchers.store') }}" class="flex flex-1 flex-col">@csrf
            <div class="flex-1 space-y-4 p-6">
                @include('voucher._form', ['prefix' => 'create', 'voucher' => null])
            </div>
            <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4 dark:border-slate-700 dark:bg-slate-800"><button type="button" class="close-voucher-drawer rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium dark:border-slate-600">Hủy</button><button class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Lưu voucher</button></div>
        </form>
    </div>

    <div id="drawer-edit-voucher" class="fixed right-0 top-0 z-40 flex h-screen w-full translate-x-full flex-col overflow-y-auto bg-white shadow-2xl transition-transform sm:max-w-lg dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700"><div><h2 class="font-bold text-slate-900 dark:text-white">Cập nhật voucher</h2><p class="mt-0.5 text-xs text-slate-500">Các thay đổi không làm mất lịch sử lượt đã dùng.</p></div><button type="button" class="close-voucher-drawer rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">✕</button></div>
        <form method="POST" id="edit-voucher-form" action="#" class="flex flex-1 flex-col">@csrf
            <div class="flex-1 space-y-4 p-6">@include('voucher._form', ['prefix' => 'edit', 'voucher' => null])</div>
            <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4 dark:border-slate-700 dark:bg-slate-800"><button type="button" class="close-voucher-drawer rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium dark:border-slate-600">Hủy</button><button class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Cập nhật</button></div>
        </form>
    </div>

    @if(session('success'))<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.(@json(session('success'))));</script>@endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const open = (id) => window.openDrawer ? window.openDrawer(id) : document.getElementById(id).classList.add('drawer-open');
            const close = (id) => window.closeDrawer ? window.closeDrawer(id) : document.getElementById(id).classList.remove('drawer-open');
            document.getElementById('open-create-voucher').addEventListener('click', () => open('drawer-create-voucher'));
            document.querySelectorAll('.close-voucher-drawer').forEach(button => button.addEventListener('click', () => ['drawer-create-voucher', 'drawer-edit-voucher'].forEach(close)));
            const routeTemplate = @json(route('vouchers.update', ':voucher'));
            const datetimeLocal = (value) => value ? value.slice(0, 16) : '';
            const syncTypeFields = (prefix) => {
                const type = document.getElementById(prefix + '-type').value;
                const valueLabel = document.getElementById(prefix + '-value-label');
                const value = document.getElementById(prefix + '-value');
                const maxBox = document.getElementById(prefix + '-max-box');
                const isShipping = type === 'free_shipping';
                value.readOnly = isShipping;
                value.classList.toggle('cursor-not-allowed', isShipping);
                value.classList.toggle('bg-slate-100', isShipping);
                if (isShipping) value.value = 0;
                valueLabel.textContent = type === 'percentage' ? 'Phần trăm giảm (%)' : 'Giá trị giảm (₫)';
                maxBox.classList.toggle('hidden', type !== 'percentage');
            };
            ['create', 'edit'].forEach(prefix => document.getElementById(prefix + '-type').addEventListener('change', () => syncTypeFields(prefix)));
            document.querySelectorAll('.edit-voucher').forEach(button => button.addEventListener('click', () => {
                const voucher = JSON.parse(button.dataset.voucher);
                const form = document.getElementById('edit-voucher-form');
                form.action = routeTemplate.replace(':voucher', voucher.id);
                ['code', 'name', 'value', 'max_discount', 'min_order_amount', 'usage_limit', 'description'].forEach(field => document.getElementById('edit-' + field).value = voucher[field] ?? '');
                document.getElementById('edit-type').value = voucher.type;
                document.getElementById('edit-starts_at').value = datetimeLocal(voucher.starts_at);
                document.getElementById('edit-ends_at').value = datetimeLocal(voucher.ends_at);
                document.getElementById('edit-status').checked = !!voucher.status;
                syncTypeFields('edit'); open('drawer-edit-voucher');
            }));
            syncTypeFields('create'); syncTypeFields('edit');
        });
    </script>
</x-app-layout>
