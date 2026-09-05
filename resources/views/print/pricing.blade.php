<x-app-layout>
    <div class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <nav class="mb-2 flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                        <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a></li>
                        <li><span class="mx-1 text-slate-400">/</span><span class="font-medium text-slate-800 dark:text-slate-200">In áo · Giá</span></li>
                    </ol>
                </nav>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">Giá in áo</h1>
                <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Chọn từng <b>phôi</b>, chọn <b>kỹ thuật in</b> rồi nhập một mức phí cố định cho mỗi áo.
                    Giá không thay đổi theo kích thước hình, vị trí mặt trước hay mặt sau.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btnSaveDraft"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:border-indigo-400 active:scale-[.98] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                    Lưu nháp
                </button>
                <button type="button" id="btnPublish"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 active:scale-[.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Xuất bản giá
                </button>
            </div>
        </div>
    </div>

    @include('print.partials.tabs')

    <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-5">
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
                <div class="border-b border-slate-200/80 bg-slate-50/60 px-5 py-4 dark:border-slate-700/80 dark:bg-slate-800/60">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bảng giá theo phôi</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Mỗi ô là phí in thêm trên một áo. Giá áo lấy từ phần thông tin phôi.</p>
                        </div>
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Không tính theo khổ</span>
                    </div>
                </div>

                <div class="space-y-3 p-4 sm:p-5">
                    @forelse ($blanks as $blank)
                        <article class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 {{ $blank->is_active ? '' : 'opacity-60' }}" data-price-blank="{{ $blank->id }}">
                            <div class="flex flex-wrap items-start justify-between gap-3 bg-slate-50/70 px-4 py-3 dark:bg-slate-900/30">
                                <div class="min-w-[220px]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $blank->name }}</h3>
                                        @unless ($blank->is_active)
                                            <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">đang tắt</span>
                                        @endunless
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                        Giá phôi: <span class="font-mono font-semibold tabular-nums">{{ number_format($blank->effectiveBasePrice(), 0, ',', '.') }} ₫</span>
                                        <span class="mx-1 text-slate-300">·</span>
                                        <span>{{ count($blank->positionKeys()) }} vị trí in</span>
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-1.5" aria-label="Vị trí in của phôi">
                                        @foreach ($blank->positionKeys() as $positionKey)
                                            <span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[10.5px] font-semibold text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ \App\Services\PrintPositions::label($positionKey) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">{{ $blank->techniques->count() }} kỹ thuật</span>
                            </div>

                            <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
                                @forelse ($blank->techniques as $technique)
                                    @php
                                        $priceRow = $simplePrices[(string) $blank->id] ?? [];
                                        $hasPrice = array_key_exists((string) $technique->id, $priceRow);
                                        $price = $hasPrice ? $priceRow[(string) $technique->id] : null;
                                    @endphp
                                    <label class="rounded-lg border border-slate-200 bg-white p-3 transition focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800/70 dark:focus-within:ring-indigo-950/50 {{ $technique->is_active ? '' : 'opacity-60' }}">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $technique->name }}</span>
                                            @unless ($technique->is_active)
                                                <span class="text-[10px] text-slate-400">đang tắt</span>
                                            @endunless
                                        </span>
                                        <span class="mt-1 block text-[10.5px] text-slate-500 dark:text-slate-400">Phí in / áo</span>
                                        <span class="mt-2 flex items-center gap-2">
                                            <input type="number" min="0" step="1000" inputmode="numeric"
                                                data-simple-price data-blank-id="{{ $blank->id }}" data-technique-id="{{ $technique->id }}"
                                                value="{{ $price }}" placeholder="Chưa nhập giá"
                                                class="w-full rounded-lg border-slate-300 text-right font-mono text-sm tabular-nums focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900/60">
                                            <span class="shrink-0 text-xs text-slate-400">₫</span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-xs text-amber-600 dark:text-amber-400">Chưa chọn kỹ thuật cho phôi này. Mở tab <b>Phôi in</b> để bật.</p>
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-5 py-8 text-center dark:border-slate-600">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Chưa có phôi in</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tạo phôi trước, sau đó quay lại đây để nhập giá.</p>
                            <a href="{{ route('print.blanks') }}" class="mt-3 inline-flex text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Mở quản lý phôi</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4 dark:border-indigo-900/60 dark:bg-indigo-950/20">
                <div class="flex gap-3">
                    <div class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-indigo-600 text-white" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m6-6H6" />
                        </svg>
                    </div>
                    <p class="text-xs leading-relaxed text-indigo-900/80 dark:text-indigo-200/80">
                        Phần <b>mặt trước / mặt sau</b> vẫn được giữ trong từng phôi. Người bán chỉ cần tick vị trí phôi nhận in ở tab <a href="{{ route('print.blanks') }}" class="font-bold underline underline-offset-2">Phôi in</a>; vị trí có thể có nhiều hình nhưng giá vẫn là một mức cố định.
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
                <div class="border-b border-slate-200/80 bg-slate-50/60 px-5 py-4 dark:border-slate-700/80 dark:bg-slate-800/60">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Lịch sử xuất bản</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Đơn cũ giữ nguyên giá đã chốt.</p>
                </div>
                <div class="overflow-x-auto p-5">
                    @if ($versions->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Chưa xuất bản lần nào.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="pb-2">Phiên bản</th><th class="pb-2">Thời điểm</th><th class="pb-2">Người xuất bản</th><th class="pb-2">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($versions as $version)
                                    <tr class="border-t border-slate-100 dark:border-slate-700/60">
                                        <td class="py-2 font-mono font-semibold text-slate-800 dark:text-slate-100">
                                            #{{ $version->id }}
                                            @if ($currentVersion && $version->id === $currentVersion->id)
                                                <span class="ml-1.5 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">đang áp dụng</span>
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

        <aside class="xl:sticky xl:top-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
                <div class="border-b border-slate-200/80 bg-slate-50/60 px-5 py-4 dark:border-slate-700/80 dark:bg-slate-800/60">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Kiểm tra nhanh</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Chọn 2 mục để xem giá một áo và tổng đơn.</p>
                </div>
                <div class="space-y-3 p-5">
                    @if ($blanks->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Tạo phôi để bắt đầu nhập giá.</p>
                    @else
                        <div>
                            <label for="simBlank" class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">1. Chọn phôi</label>
                            <select id="simBlank" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-900/60">
                                @foreach ($blanks as $blank)
                                    <option value="{{ $blank->id }}" data-techniques="{{ $blank->techniques->pluck('id')->implode(',') }}" @disabled(!$blank->is_active)>
                                        {{ $blank->name }}{{ $blank->is_active ? '' : ' · đang tắt' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="simTechnique" class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">2. Chọn kỹ thuật in</label>
                            <select id="simTechnique" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-900/60"></select>
                        </div>
                        <div>
                            <label for="simQty" class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">Số lượng</label>
                            <input type="number" id="simQty" min="1" value="1" class="w-full rounded-lg border-slate-300 text-right font-mono text-sm tabular-nums dark:border-slate-600 dark:bg-slate-900/60">
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
                            <div id="simLines" class="space-y-1.5 text-[12.5px]"></div>
                            <div class="mt-3 flex items-baseline justify-between border-t-2 border-slate-800 pt-3 dark:border-slate-200">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Giá mỗi áo</span>
                                <span id="simUnit" class="font-mono text-xl font-bold tabular-nums text-slate-900 dark:text-white">—</span>
                            </div>
                            <div class="mt-1.5 flex items-baseline justify-between">
                                <span class="text-xs text-slate-500 dark:text-slate-400">Tổng đơn</span>
                                <span id="simTotal" class="font-mono text-sm font-semibold tabular-nums text-slate-600 dark:text-slate-300">—</span>
                            </div>
                        </div>
                        <p class="border-l-2 border-indigo-500 pl-3 text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400">Giá thử trên bản nháp. Bấm <b>Xuất bản giá</b> sau khi kiểm tra xong.</p>
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
        const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));

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

        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');

        function collectPrices() {
            const prices = {};
            document.querySelectorAll('[data-simple-price]').forEach(input => {
                const blankId = input.dataset.blankId;
                const techniqueId = input.dataset.techniqueId;
                prices[blankId] = prices[blankId] || {};
                prices[blankId][techniqueId] = input.value.trim() === ''
                    ? null
                    : Math.max(0, parseInt(input.value, 10) || 0);
            });
            return prices;
        }

        document.getElementById('btnSaveDraft')?.addEventListener('click', async event => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
                const result = await post('{{ route('print.pricing.draft') }}', {
                    mode: 'simple',
                    blank_technique_prices: collectPrices(),
                });
                toast(result.success);
            } catch (error) {
                toast(error.message, false);
            } finally {
                button.disabled = false;
            }
        });

        document.getElementById('btnPublish')?.addEventListener('click', async event => {
            const note = window.prompt('Ghi chú cho phiên bản này (bỏ trống cũng được):', 'Giá cố định theo phôi và kỹ thuật');
            if (note === null) return;

            const button = event.currentTarget;
            button.disabled = true;
            try {
                await post('{{ route('print.pricing.draft') }}', {
                    mode: 'simple',
                    blank_technique_prices: collectPrices(),
                });
                const result = await post('{{ route('print.pricing.publish') }}', { note });
                toast(result.success);
                setTimeout(() => location.reload(), 900);
            } catch (error) {
                toast(error.message, false);
                button.disabled = false;
            }
        });

        const simBlank = document.getElementById('simBlank');
        const simTechnique = document.getElementById('simTechnique');
        const techniqueOptions = @json($techniques->where('is_active', true)->map(fn ($technique) => ['id' => $technique->id, 'name' => $technique->name])->values());

        function fillTechniques() {
            if (!simBlank || !simTechnique) return;
            const allowed = (simBlank.selectedOptions[0]?.dataset.techniques || '').split(',').filter(Boolean);
            const usable = techniqueOptions.filter(technique => allowed.includes(String(technique.id)));
            simTechnique.innerHTML = usable.length
                ? usable.map(technique => '<option value="' + technique.id + '">' + escapeHtml(technique.name) + '</option>').join('')
                : '<option value="">— phôi chưa có kỹ thuật —</option>';
            runSim();
        }

        async function runSim() {
            if (!simBlank || !simTechnique?.value) return;
            try {
                const result = await post('{{ route('print.pricing.simulate') }}', {
                    blank_id: simBlank.value,
                    technique_id: simTechnique.value,
                    qty: parseInt(document.getElementById('simQty').value, 10) || 1,
                });

                const errorRows = (result.errors || []).map(error => '<div class="text-rose-600 dark:text-rose-400">✕ ' + escapeHtml(error) + '</div>');
                const warningRows = (result.warnings || []).map(warning => '<div class="text-amber-600 dark:text-amber-400">! ' + escapeHtml(warning) + '</div>');
                const rows = (result.lines || []).map(line => '<div class="flex justify-between gap-3 text-slate-700 dark:text-slate-200"><span>' + escapeHtml(line.label) + '</span><span class="whitespace-nowrap font-mono tabular-nums">' + vnd(line.amount) + '</span></div>');

                document.getElementById('simLines').innerHTML = errorRows.concat(warningRows, rows).join('') || '<span class="text-slate-400">Chưa có dữ liệu giá.</span>';
                document.getElementById('simUnit').textContent = result.errors?.length ? '—' : vnd(result.unit_price);
                document.getElementById('simTotal').textContent = result.errors?.length ? '—' : vnd(result.total);
            } catch (error) {
                toast(error.message, false);
            }
        }

        simBlank?.addEventListener('change', fillTechniques);
        simTechnique?.addEventListener('change', runSim);
        document.getElementById('simQty')?.addEventListener('input', runSim);
        fillTechniques();
    })();
    </script>
</x-app-layout>
