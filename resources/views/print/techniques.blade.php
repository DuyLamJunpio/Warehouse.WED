<x-app-layout>
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                </li>
                <li>
                    <span class="mx-1 text-slate-400">/</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Kỹ thuật</span>
                </li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            Kỹ thuật in &amp; bậc khổ
        </h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
            Ràng buộc của kỹ thuật là <b>dữ liệu, không phải mã</b>: số màu tối đa, có nhận ảnh chụp không,
            DPI tối thiểu. Studio bên web đọc đúng mấy trường này để chặn khách, nên tạo thêm một kỹ thuật lạ
            không cần ai sửa code.
        </p>
    </div>

    @include('print.partials.tabs')

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
        <div class="space-y-5">

            {{-- Danh sách kỹ thuật --}}
            <div class="space-y-3">
                @forelse ($techniques as $technique)
                    @php($use = $usage[$technique->id])
                    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5 {{ $technique->is_active ? '' : 'opacity-60' }}"
                        data-technique="{{ $technique->id }}">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" data-toggle @checked($technique->is_active) class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                            <div class="flex-1 min-w-[180px]">
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $technique->name }}</h2>
                                @if ($technique->description)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $technique->description }}</p>
                                @endif
                            </div>
                            @if ($use['priced'] === 0)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">chưa có giá</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    {{ $use['priced'] }}/{{ $tiers->count() }} bậc khổ
                                </span>
                            @endif
                        </div>

                        {{-- Ràng buộc, hiện dưới dạng thẻ để đọc lướt --}}
                        <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                DPI ≥ <span class="font-mono font-semibold">{{ $technique->min_dpi }}</span>
                            </span>
                            <span class="px-2 py-1 rounded-md {{ $technique->max_colors === null ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300' }}">
                                {{ $technique->max_colors === null ? 'không giới hạn màu' : 'tối đa ' . $technique->max_colors . ' màu' }}
                            </span>
                            <span class="px-2 py-1 rounded-md {{ $technique->accepts_photo ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' }}">
                                ảnh chụp {{ $technique->accepts_photo ? '✓' : '✕' }}
                            </span>
                            <span class="px-2 py-1 rounded-md {{ $technique->accepts_gradient ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' }}">
                                chuyển màu {{ $technique->accepts_gradient ? '✓' : '✕' }}
                            </span>
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                {{ $technique->needs_underbase ? 'cần lót trắng áo tối' : 'không cần lót' }}
                            </span>
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                MOQ <span class="font-mono font-semibold">{{ $technique->moq }}</span>
                            </span>
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                <span class="font-mono font-semibold">{{ $technique->lead_days }}</span> ngày
                            </span>
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 uppercase">
                                {{ $technique->file_types }}
                            </span>
                        </div>

                        {{-- Tham chiếu treo: nói rõ tắt cái này thì ảnh hưởng gì --}}
                        <p class="mt-3 text-[11.5px] text-slate-500 dark:text-slate-400">
                            Đang được dùng bởi <b>{{ $use['blanks'] }} phôi</b> và <b>{{ $use['rules'] }} quy tắc giá</b>.
                            @unless ($technique->is_active)
                                <span class="text-amber-600 dark:text-amber-400">Đã tắt — ẩn khỏi web, dữ liệu cũ giữ nguyên.</span>
                            @endunless
                        </p>
                    </section>
                @empty
                    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-6">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Chưa có kỹ thuật in nào. Tạo thủ công ở khung bên phải, hoặc nạp sẵn bốn kỹ thuật
                            thông dụng với ràng buộc điền đúng:
                        </p>
                        <code class="mt-2 inline-block px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 text-xs">php artisan db:seed --class=PrintModuleSeeder</code>
                    </section>
                @endforelse
            </div>

            {{-- Bậc khổ --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bậc khổ in</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Khung bao của khách được xếp vào bậc <b>nhỏ nhất chứa được</b>. Sửa kích thước ở đây
                        không đụng tới đơn cũ — chúng mang theo ảnh chụp bảng giá của chính mình.
                    </p>
                </div>
                <div class="p-5 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="pb-2">Bật</th>
                                <th class="pb-2">Tên</th>
                                <th class="pb-2 text-right">Rộng tối đa (mm)</th>
                                <th class="pb-2 text-right">Cao tối đa (mm)</th>
                                <th class="pb-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tiers as $tier)
                                <tr class="border-t border-slate-100 dark:border-slate-700/60" data-tier="{{ $tier->id }}">
                                    <td class="py-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" data-tier-toggle @checked($tier->is_active) class="sr-only peer">
                                            <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </td>
                                    <td class="py-2">
                                        <input type="text" data-tier-name value="{{ $tier->name }}"
                                            class="w-24 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right">
                                        <input type="number" min="1" data-tier-w value="{{ $tier->width_mm }}"
                                            class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right">
                                        <input type="number" min="1" data-tier-h value="{{ $tier->height_mm }}"
                                            class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right">
                                        <button type="button" data-tier-save
                                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lưu</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-end gap-2">
                        <div>
                            <label for="newTierName" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên</label>
                            <input type="text" id="newTierName" placeholder="A2"
                                class="w-24 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                        </div>
                        <div>
                            <label for="newTierW" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng (mm)</label>
                            <input type="number" id="newTierW" min="1" value="420"
                                class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                        </div>
                        <div>
                            <label for="newTierH" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Cao (mm)</label>
                            <input type="number" id="newTierH" min="1" value="594"
                                class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                        </div>
                        <button type="button" id="btnAddTier"
                            class="px-4 py-2 text-sm font-semibold text-white bg-slate-800 dark:bg-slate-200 dark:text-slate-900 rounded-lg hover:bg-slate-700 dark:hover:bg-white transition-colors">
                            Thêm bậc khổ
                        </button>
                    </div>
                </div>
            </section>
        </div>

        {{-- Tạo kỹ thuật mới --}}
        <aside class="xl:sticky xl:top-6">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Thêm kỹ thuật</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <label for="ntName" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên kỹ thuật</label>
                        <input type="text" id="ntName" placeholder="VD: In chuyển nhiệt 3D"
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                    </div>
                    <div>
                        <label for="ntDesc" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Mô tả cho khách</label>
                        <textarea id="ntDesc" rows="2" placeholder="Một câu ngắn hiện trong studio."
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ntMax" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Số màu tối đa</label>
                            <input type="number" id="ntMax" min="1" placeholder="trống = không giới hạn"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                        <div>
                            <label for="ntDpi" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">DPI tối thiểu</label>
                            <input type="number" id="ntDpi" min="30" value="150"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ntLead" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Ngày làm</label>
                            <input type="number" id="ntLead" min="0" value="3"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                        <div>
                            <label for="ntMoq" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">MOQ</label>
                            <input type="number" id="ntMoq" min="1" value="1"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="ntFiles" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Định dạng nhận</label>
                        <input type="text" id="ntFiles" value="png,pdf,svg"
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                    </div>
                    <div class="space-y-2 pt-1">
                        <label class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                            <input type="checkbox" id="ntPhoto" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            Nhận ảnh chụp
                        </label>
                        <label class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                            <input type="checkbox" id="ntGrad" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            Nhận chuyển màu
                        </label>
                        <label class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                            <input type="checkbox" id="ntUnder" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            Cần lót trắng trên áo tối
                        </label>
                    </div>
                    <button type="button" id="btnAddTechnique"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        Tạo kỹ thuật
                    </button>
                    <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
                        Kỹ thuật mới sinh ra một <b>cột rỗng</b> trong ma trận giá. Chưa điền giá thì khách
                        chưa chọn được — thẻ kỹ thuật sẽ hiện dấu "chưa có giá".
                    </p>
                </div>
            </section>
        </aside>
    </div>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

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

        // ── Bật/tắt kỹ thuật ─────────────────────────────────────────
        document.querySelectorAll('[data-technique] [data-toggle]').forEach(cb => {
            cb.addEventListener('change', async () => {
                const card = cb.closest('[data-technique]');
                try {
                    const r = await post('/print/techniques/' + card.dataset.technique + '/toggle', { is_active: cb.checked });
                    card.classList.toggle('opacity-60', !cb.checked);
                    toast(r.success);
                } catch (err) {
                    // Trả nút về đúng trạng thái thật: để nó hiện "đã tắt" trong
                    // khi máy chủ vẫn đang bật là kiểu sai nguy hiểm nhất.
                    cb.checked = !cb.checked;
                    toast(err.message, false);
                }
            });
        });

        // ── Bậc khổ ──────────────────────────────────────────────────
        document.querySelectorAll('[data-tier]').forEach(row => {
            row.querySelector('[data-tier-save]')?.addEventListener('click', async () => {
                try {
                    const r = await post('/print/tiers/' + row.dataset.tier, {
                        name: row.querySelector('[data-tier-name]').value,
                        width_mm: parseInt(row.querySelector('[data-tier-w]').value, 10) || 1,
                        height_mm: parseInt(row.querySelector('[data-tier-h]').value, 10) || 1,
                    });
                    toast(r.success);
                } catch (err) { toast(err.message, false); }
            });

            row.querySelector('[data-tier-toggle]')?.addEventListener('change', async (e) => {
                try {
                    const r = await post('/print/tiers/' + row.dataset.tier + '/toggle', { is_active: e.target.checked });
                    toast(r.success);
                } catch (err) {
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                }
            });
        });

        document.getElementById('btnAddTier')?.addEventListener('click', async (e) => {
            const name = document.getElementById('newTierName').value.trim();
            if (!name) { document.getElementById('newTierName').focus(); return; }
            e.currentTarget.disabled = true;
            try {
                const r = await post('{{ route('print.tiers.store') }}', {
                    name,
                    width_mm: parseInt(document.getElementById('newTierW').value, 10) || 1,
                    height_mm: parseInt(document.getElementById('newTierH').value, 10) || 1,
                });
                toast(r.success);
                setTimeout(() => location.reload(), 900);
            } catch (err) { toast(err.message, false); }
            e.currentTarget.disabled = false;
        });

        // ── Tạo kỹ thuật ─────────────────────────────────────────────
        document.getElementById('btnAddTechnique')?.addEventListener('click', async (e) => {
            const name = document.getElementById('ntName').value.trim();
            if (!name) { document.getElementById('ntName').focus(); return; }

            // Ô số màu để trống nghĩa là KHÔNG giới hạn, không phải 0 màu.
            const maxRaw = document.getElementById('ntMax').value;

            e.currentTarget.disabled = true;
            try {
                const r = await post('{{ route('print.techniques.store') }}', {
                    name,
                    description: document.getElementById('ntDesc').value.trim() || null,
                    max_colors: maxRaw === '' ? null : parseInt(maxRaw, 10),
                    min_dpi: parseInt(document.getElementById('ntDpi').value, 10) || 150,
                    file_types: document.getElementById('ntFiles').value.trim() || 'png',
                    lead_days: parseInt(document.getElementById('ntLead').value, 10) || 0,
                    moq: parseInt(document.getElementById('ntMoq').value, 10) || 1,
                    accepts_photo: document.getElementById('ntPhoto').checked,
                    accepts_gradient: document.getElementById('ntGrad').checked,
                    needs_underbase: document.getElementById('ntUnder').checked,
                });
                toast(r.success);
                setTimeout(() => location.reload(), 1200);
            } catch (err) { toast(err.message, false); }
            e.currentTarget.disabled = false;
        });
    })();
    </script>
</x-app-layout>
