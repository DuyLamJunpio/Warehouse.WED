<x-app-layout>
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                        <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                        </li>
                        <li>
                            <span class="mx-1 text-slate-400">/</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Bảng giá</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Bảng giá in
                </h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
                    Sáu bước tính tiền chạy theo thứ tự cố định: giá phôi → giá in cơ bản → phụ phí cộng →
                    hệ số nhân → chiết khấu số lượng → làm tròn. Sửa ở đây là sửa vào <b>bản nháp</b>;
                    khách chỉ thấy giá mới sau khi bấm Xuất bản.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btnSaveDraft"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:border-indigo-400 rounded-xl shadow-xs transition-all">
                    Lưu nháp
                </button>
                <button type="button" id="btnPublish"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Xuất bản</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Tabs của module --}}
    @include('print.partials.tabs')

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
        <div class="space-y-5">

            {{-- Ma trận giá --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Ma trận giá in</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Hàng là bậc khổ, cột là kỹ thuật. <b>Ô để trống khác ô 0 đồng</b> — trống nghĩa là
                        kỹ thuật đó không nhận khổ đó và studio tự ẩn lựa chọn với khách.
                    </p>
                </div>
                <div class="p-5 overflow-x-auto">
                    @if ($techniques->isEmpty() || $tiers->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Chưa có kỹ thuật in hoặc bậc khổ nào.
                            <a href="{{ route('print.techniques') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Tạo ở đây</a>,
                            hoặc chạy <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-xs">php artisan db:seed --class=PrintModuleSeeder</code>
                            để nạp sẵn 4 kỹ thuật và 4 bậc khổ thông dụng.
                        </p>
                    @else
                        <table class="w-full text-sm" id="matrix">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-2.5 pr-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Bậc khổ</th>
                                    @foreach ($techniques as $technique)
                                        <th class="pb-2.5 px-2 text-right text-[11px] font-bold uppercase tracking-wider {{ $technique->is_active ? 'text-slate-400' : 'text-slate-300 dark:text-slate-600 line-through' }}">
                                            {{ $technique->name }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tiers as $tier)
                                    <tr class="border-t border-slate-100 dark:border-slate-700/60">
                                        <td class="py-2.5 pr-4 whitespace-nowrap">
                                            <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $tier->name }}</span>
                                            <span class="ml-1.5 text-[11px] tabular-nums text-slate-400">≤{{ $tier->width_mm }}×{{ $tier->height_mm }}mm</span>
                                        </td>
                                        @foreach ($techniques as $technique)
                                            @php($value = $draft['cells'][$technique->id][$tier->id] ?? null)
                                            <td class="py-2 px-2 text-right">
                                                <input type="number" step="1000" min="0" placeholder="—"
                                                    data-cell data-technique="{{ $technique->id }}" data-tier="{{ $tier->id }}"
                                                    value="{{ $value }}"
                                                    class="w-28 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            {{-- Quy tắc phụ phí --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Quy tắc phụ phí</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Ngữ pháp đóng: điều kiện do hệ thống dựng, ở đây bật/tắt và chỉnh con số.
                        Cố ý không cho gõ công thức tự do — bản PHP và bản TypeScript bên web phải tính ra
                        cùng một số đến từng đồng.
                    </p>
                </div>
                <div class="p-5 space-y-3" id="ruleList">
                    @forelse ($draft['rules'] as $rule)
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 {{ ($rule['enabled'] ?? false) ? '' : 'opacity-60' }}"
                            data-rule="{{ $rule['id'] }}"
                            data-label="{{ $rule['label'] }}"
                            data-when="{{ json_encode($rule['when'] ?? [], JSON_UNESCAPED_UNICODE) }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" data-rule-enabled @checked($rule['enabled'] ?? false) class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                                <span class="flex-1 min-w-[160px] text-sm font-semibold text-slate-900 dark:text-white">{{ $rule['label'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300">
                                    {{ $perLabels[$rule['apply']['per']] ?? $rule['apply']['per'] }}
                                </span>
                            </div>

                            <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-900/50 px-3 py-2 text-[11.5px] font-mono leading-relaxed text-slate-500 dark:text-slate-400">
                                @include('print.partials.rule-condition', ['when' => $rule['when'] ?? [], 'techniques' => $techniques])
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                <span class="text-xs font-bold text-slate-400">THÌ</span>
                                <select data-rule-kind class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    <option value="add" @selected($rule['apply']['kind'] === 'add')>cộng tiền</option>
                                    <option value="multiply" @selected($rule['apply']['kind'] === 'multiply')>nhân hệ số</option>
                                    <option value="percent" @selected($rule['apply']['kind'] === 'percent')>cộng %</option>
                                </select>
                                <input type="number" data-rule-amount value="{{ $rule['apply']['amount'] }}"
                                    step="{{ $rule['apply']['kind'] === 'multiply' ? '0.05' : ($rule['apply']['kind'] === 'percent' ? '1' : '1000') }}"
                                    class="w-28 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                <select data-rule-per class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    @foreach ($perLabels as $key => $label)
                                        <option value="{{ $key }}" @selected($rule['apply']['per'] === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Chưa có quy tắc nào. Chạy
                            <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-xs">php artisan db:seed --class=PrintModuleSeeder</code>
                            để nạp vài quy tắc mẫu có thật trong nghề.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- Chiết khấu + làm tròn --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Chiết khấu số lượng</h2>
                    </div>
                    <div class="p-5">
                        <table class="w-full text-sm" id="qtyTable">
                            <thead>
                                <tr>
                                    <th class="pb-2 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Từ số lượng</th>
                                    <th class="pb-2 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Giảm (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($draft['qty_tiers'] as $q)
                                    <tr data-qt>
                                        <td class="py-1.5 text-right">
                                            <input type="number" min="1" data-qt-from value="{{ $q['from'] }}"
                                                class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        </td>
                                        <td class="py-1.5 text-right">
                                            <input type="number" min="0" max="100" step="0.5" data-qt-pct value="{{ $q['pct'] }}"
                                                class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" id="btnAddQty"
                            class="mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">+ Thêm bậc</button>
                    </div>
                </section>

                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Làm tròn &amp; sàn giá</h2>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label for="rounding" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Làm tròn lên bội số (đồng)</label>
                            <input type="number" id="rounding" min="0" step="500" value="{{ $draft['rounding'] }}"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                        <div>
                            <label for="minCharge" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Sàn giá mỗi áo (đồng)</label>
                            <input type="number" id="minCharge" min="0" step="10000" value="{{ $draft['min_charge'] }}"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                            <p class="mt-1 text-[11px] text-slate-400">0 = không đặt sàn.</p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Lịch sử phiên bản --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Phiên bản đã xuất bản</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Mỗi đơn giữ id phiên bản đã dùng, nên sửa giá hôm nay không đổi tiền của đơn hôm qua.
                    </p>
                </div>
                <div class="p-5 overflow-x-auto">
                    @if ($versions->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Chưa xuất bản lần nào — web đang tạm dùng bản nháp để báo giá.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="pb-2">Phiên bản</th>
                                    <th class="pb-2">Thời điểm</th>
                                    <th class="pb-2">Người xuất bản</th>
                                    <th class="pb-2">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($versions as $version)
                                    <tr class="border-t border-slate-100 dark:border-slate-700/60">
                                        <td class="py-2 font-mono font-semibold text-slate-800 dark:text-slate-100">
                                            #{{ $version->id }}
                                            @if ($currentVersion && $version->id === $currentVersion->id)
                                                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">đang áp dụng</span>
                                            @endif
                                        </td>
                                        <td class="py-2 tabular-nums text-slate-500 dark:text-slate-400">{{ $version->published_at?->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 text-slate-500 dark:text-slate-400">{{ $version->publisher->name ?? '—' }}</td>
                                        <td class="py-2 text-slate-500 dark:text-slate-400">{{ $version->note ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>

        {{-- Trình thử giá --}}
        <aside class="xl:sticky xl:top-6">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Trình thử giá</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tính trên bản nháp — xem con số vừa gõ ra bao nhiêu trước khi xuất bản.</p>
                </div>
                <div class="p-5 space-y-3">
                    @if ($blanks->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Chưa có phôi in nào để thử giá. Màn hình quản lý phôi đang được dựng ở bước tiếp theo.
                        </p>
                    @else
                        <div>
                            <label for="simBlank" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Phôi</label>
                            <select id="simBlank" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                                @foreach ($blanks as $blank)
                                    <option value="{{ $blank->id }}"
                                        data-positions="{{ json_encode($blank->positionKeys()) }}">
                                        {{ $blank->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="simTechnique" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Kỹ thuật</label>
                            <select id="simTechnique" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                                @foreach ($techniques->where('is_active', true) as $technique)
                                    <option value="{{ $technique->id }}">{{ $technique->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="simPosition" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Vị trí in</label>
                            <select id="simPosition" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm"></select>
                        </div>
                        <div>
                            <label for="simTier" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Bậc khổ</label>
                            <select id="simTier" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                                @foreach ($tiers->where('is_active', true) as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="simTone" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tông áo</label>
                                <select id="simTone" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                                    <option value="light">Sáng</option>
                                    <option value="dark">Tối</option>
                                </select>
                            </div>
                            <div>
                                <label for="simQty" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Số lượng</label>
                                <input type="number" id="simQty" min="1" value="20"
                                    class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                            </div>
                        </div>

                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 p-4">
                            <div id="simLines" class="space-y-1.5 text-[12.5px]"></div>
                            <div class="mt-3 pt-3 border-t-2 border-slate-800 dark:border-slate-200 flex items-baseline justify-between">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mỗi áo</span>
                                <span id="simUnit" class="font-mono text-xl font-bold tabular-nums text-slate-900 dark:text-white">—</span>
                            </div>
                            <div class="mt-1.5 flex items-baseline justify-between">
                                <span class="text-xs text-slate-500 dark:text-slate-400">Tổng đơn</span>
                                <span id="simTotal" class="font-mono text-sm font-semibold tabular-nums text-slate-600 dark:text-slate-300">—</span>
                            </div>
                        </div>

                        <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
                            Lưu vài cấu hình ở đây làm <b>bộ ca kiểm thử chuẩn</b>, chạy trên cả PHP lẫn
                            TypeScript. Hai bên phải ra cùng một số đến từng đồng.
                        </p>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
        const vnd = n => new Intl.NumberFormat('vi-VN').format(Math.round(n)) + ' ₫';

        const post = async (url, body) => {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.error || data.message || 'Máy chủ trả về HTTP ' + res.status);
            return data;
        };

        // Dùng hộp thông báo chung của trang quản trị (resources/js/admin.js)
        // thay vì tự dựng: cùng một kiểu báo ở mọi màn hình.
        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');

        /**
         * Gom màn hình thành đúng hình dạng bản nháp mà server chờ.
         *
         * `when` đọc lại từ data-when rồi gửi nguyên vẹn: giao diện không sửa
         * điều kiện, nhưng bỏ quên nó khi lưu là mỗi lần bấm Lưu lại xoá mất
         * phần điều kiện của mọi quy tắc.
         */
        function collectDraft() {
            const cells = {};
            document.querySelectorAll('[data-cell]').forEach(input => {
                if (input.value === '') return;
                const t = input.dataset.technique, tier = input.dataset.tier;
                cells[t] = cells[t] || {};
                cells[t][tier] = Math.max(0, parseInt(input.value, 10) || 0);
            });

            const rules = Array.from(document.querySelectorAll('[data-rule]')).map(el => ({
                id: el.dataset.rule,
                label: el.dataset.label,
                enabled: el.querySelector('[data-rule-enabled]').checked,
                when: JSON.parse(el.dataset.when || '{}'),
                apply: {
                    kind: el.querySelector('[data-rule-kind]').value,
                    amount: parseFloat(el.querySelector('[data-rule-amount]').value) || 0,
                    per: el.querySelector('[data-rule-per]').value,
                },
            }));

            const qtyTiers = Array.from(document.querySelectorAll('[data-qt]')).map(row => ({
                from: parseInt(row.querySelector('[data-qt-from]').value, 10) || 1,
                pct: parseFloat(row.querySelector('[data-qt-pct]').value) || 0,
            }));

            return {
                cells, rules, qty_tiers: qtyTiers,
                rounding: parseInt(document.getElementById('rounding').value, 10) || 0,
                min_charge: parseInt(document.getElementById('minCharge').value, 10) || 0,
            };
        }

        document.getElementById('btnSaveDraft')?.addEventListener('click', async (e) => {
            e.currentTarget.disabled = true;
            try {
                const r = await post('{{ route('print.pricing.draft') }}', collectDraft());
                toast(r.success);
            } catch (err) { toast(err.message, false); }
            e.currentTarget.disabled = false;
        });

        document.getElementById('btnPublish')?.addEventListener('click', async (e) => {
            const note = prompt('Ghi chú cho phiên bản này (bỏ trống cũng được):', '');
            if (note === null) return;
            e.currentTarget.disabled = true;
            try {
                // Lưu nháp trước rồi mới xuất bản: nếu không thì con số vừa gõ
                // chưa vào CSDL và phiên bản mới chụp lại đúng bản cũ.
                await post('{{ route('print.pricing.draft') }}', collectDraft());
                const r = await post('{{ route('print.pricing.publish') }}', { note });
                toast(r.success);
                setTimeout(() => location.reload(), 1200);
            } catch (err) { toast(err.message, false); }
            e.currentTarget.disabled = false;
        });

        document.getElementById('btnAddQty')?.addEventListener('click', () => {
            const tbody = document.querySelector('#qtyTable tbody');
            const tr = document.createElement('tr');
            tr.setAttribute('data-qt', '');
            const cls = 'w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5';
            tr.innerHTML =
                '<td class="py-1.5 text-right"><input type="number" min="1" data-qt-from value="100" class="' + cls + '"></td>' +
                '<td class="py-1.5 text-right"><input type="number" min="0" max="100" step="0.5" data-qt-pct value="20" class="' + cls + '"></td>';
            tbody.appendChild(tr);
        });

        // ── Trình thử giá ────────────────────────────────────────────
        const simBlank = document.getElementById('simBlank');
        const simPosition = document.getElementById('simPosition');

        // Bốn vị trí là hằng số phía máy chủ; phôi chỉ bật/tắt từng cái.
        const POSITIONS = @json(collect($positions)->map(fn ($p) => ['key' => $p['key'], 'label' => $p['label']]));

        function fillPositions() {
            if (!simBlank || !simPosition) return;
            const allowed = JSON.parse(simBlank.selectedOptions[0]?.dataset.positions || '[]');
            const usable = POSITIONS.filter(p => allowed.includes(p.key));
            simPosition.innerHTML = usable.length
                ? usable.map(p => '<option value="' + p.key + '">' + p.label + '</option>').join('')
                : '<option value="">— phôi đang tắt hết vị trí in —</option>';
        }

        async function runSim() {
            if (!simBlank || !simPosition?.value) return;
            try {
                const r = await post('{{ route('print.pricing.simulate') }}', {
                    blank_id: simBlank.value,
                    technique_id: document.getElementById('simTechnique').value,
                    position_key: simPosition.value,
                    tier_id: document.getElementById('simTier').value,
                    tone: document.getElementById('simTone').value,
                    qty: parseInt(document.getElementById('simQty').value, 10) || 1,
                });

                const rows = r.lines.map(l => {
                    const tone = l.sub ? 'pl-3.5 text-slate-500 dark:text-slate-400' : 'text-slate-700 dark:text-slate-200';
                    const money = l.amount < 0 ? 'text-emerald-600 dark:text-emerald-400' : '';
                    const meta = l.meta ? '<span class="block text-[10.5px] text-slate-400">' + l.meta + '</span>' : '';
                    return '<div class="flex justify-between gap-3 ' + tone + '">' +
                        '<span>' + (l.sub ? '↳ ' : '') + l.label + meta + '</span>' +
                        '<span class="font-mono tabular-nums whitespace-nowrap ' + money + '">' +
                        (l.amount < 0 ? '−' : '') + vnd(Math.abs(l.amount)) + '</span></div>';
                });

                const errs = (r.errors || []).map(e =>
                    '<div class="text-rose-600 dark:text-rose-400">✕ ' + e + '</div>');

                document.getElementById('simLines').innerHTML = errs.concat(rows).join('');
                document.getElementById('simUnit').textContent = vnd(r.unit_price);
                document.getElementById('simTotal').textContent = vnd(r.total);
            } catch (err) { toast(err.message, false); }
        }

        ['simBlank', 'simTechnique', 'simPosition', 'simTier', 'simTone', 'simQty'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => {
                if (id === 'simBlank') fillPositions();
                runSim();
            });
        });

        fillPositions();
        runSim();
    })();
    </script>
</x-app-layout>
