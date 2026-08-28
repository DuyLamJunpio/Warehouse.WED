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
                        data-technique="{{ $technique->id }}"
                        data-technique-name="{{ $technique->name }}"
                        data-technique-description="{{ $technique->description ?? '' }}"
                        data-technique-max-colors="{{ $technique->max_colors ?? '' }}"
                        data-technique-min-dpi="{{ $technique->min_dpi }}"
                        data-technique-file-types="{{ $technique->file_types }}"
                        data-technique-lead-days="{{ $technique->lead_days }}"
                        data-technique-moq="{{ $technique->moq }}"
                        data-technique-accepts-photo="{{ $technique->accepts_photo ? '1' : '0' }}"
                        data-technique-accepts-gradient="{{ $technique->accepts_gradient ? '1' : '0' }}"
                        data-technique-needs-underbase="{{ $technique->needs_underbase ? '1' : '0' }}">
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
                            <div class="flex items-center gap-2 ml-auto shrink-0">
                                <button type="button" data-technique-edit
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/70 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors">
                                    Sửa
                                </button>
                                <button type="button" data-technique-delete data-technique-name="{{ $technique->name }}"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/70 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                    Xóa
                                </button>
                            </div>
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
                            Đang được dùng bởi <b>{{ $use['blanks'] }} phôi</b>, <b>{{ $use['designs'] }} thiết kế</b> và <b>{{ $use['rules'] }} quy tắc giá</b>.
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
                                        <input type="text" data-tier-name value="{{ $tier->name }}" disabled
                                            class="w-24 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right">
                                        <input type="number" min="1" data-tier-w value="{{ $tier->width_mm }}" disabled
                                            class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right">
                                        <input type="number" min="1" data-tier-h value="{{ $tier->height_mm }}" disabled
                                            class="w-24 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" data-tier-edit
                                                class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Sửa</button>
                                            <button type="button" data-tier-save
                                                class="hidden text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lưu</button>
                                            <button type="button" data-tier-cancel
                                                class="hidden text-xs font-semibold text-slate-500 dark:text-slate-400 hover:underline">Hủy</button>
                                            <button type="button" data-tier-delete data-tier-name="{{ $tier->name }}"
                                                class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">Xóa</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="newTierForm" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-end gap-2">
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

        {{-- Tạo / sửa kỹ thuật --}}
        <aside class="xl:sticky xl:top-6">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 id="techniqueFormTitle" class="text-sm font-bold text-slate-900 dark:text-white">Thêm kỹ thuật</h2>
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
                    <div class="flex gap-2">
                        <button type="button" id="btnAddTechnique"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        Tạo kỹ thuật
                        </button>
                        <button type="button" id="btnCancelTechniqueEdit" hidden
                            class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            Hủy
                        </button>
                    </div>
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

        const request = async (url, method, body) => {
            const options = {
                method,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            };

            if (body !== undefined) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }

            const res = await fetch(url, {
                ...options,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const validation = data.errors ? Object.values(data.errors).flat().join('\n') : '';
                throw new Error(data.error || validation || data.message || 'Máy chủ trả về HTTP ' + res.status);
            }
            return data;
        };

        const post = (url, body) => request(url, 'POST', body);
        const del = url => request(url, 'DELETE');

        // Dùng hộp thông báo chung của trang quản trị (resources/js/admin.js)
        // thay vì tự dựng: cùng một kiểu báo ở mọi màn hình.
        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');

        // ── Hiệu ứng loading dùng chung cho mọi thao tác DB ──────────
        const SPIN =
            '<svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none">'
            + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
            + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>';

        // Mỗi vùng chỉ chạy một request tại một thời điểm.
        const running = new WeakSet();

        // Phủ mờ vùng đang chờ để người dùng không sửa dữ liệu nửa chừng.
        const veil = (host, label) => {
            if (!host) return () => {};

            const pinned = getComputedStyle(host).position === 'static';
            if (pinned) host.classList.add('relative');

            const previousBusy = host.getAttribute('aria-busy');
            host.setAttribute('aria-busy', 'true');

            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 z-30 flex items-center justify-center gap-2 rounded-[inherit] '
                + 'bg-white/70 dark:bg-slate-900/70 backdrop-blur-[1px] cursor-wait '
                + 'text-sm font-semibold text-slate-700 dark:text-slate-200';
            overlay.innerHTML = SPIN + (label ? '<span>' + label + '</span>' : '');
            host.appendChild(overlay);

            return () => {
                overlay.remove();
                if (previousBusy === null) host.removeAttribute('aria-busy');
                else host.setAttribute('aria-busy', previousBusy);
                if (pinned) host.classList.remove('relative');
            };
        };

        /**
         * Chạy request DB kèm spinner trên nút và overlay trên vùng liên quan.
         * Với thao tác reload, giữ nguyên loading cho tới khi trang mới tải xong.
         */
        const withBusy = async (opts, work) => {
            const guard = opts.guard || opts.host || opts.btn;
            if (guard && running.has(guard)) return null;
            if (guard) running.add(guard);

            const btn = opts.btn || null;
            const before = btn ? btn.innerHTML : '';
            const label = opts.label || 'Đang xử lý...';
            const controls = Array.from(new Set(opts.controls || []))
                .filter(control => control && control !== btn);
            const controlStates = controls.map(control => ({ control, disabled: control.disabled }));

            controls.forEach(control => { control.disabled = true; });

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                btn.innerHTML = '<span class="inline-flex items-center justify-center gap-2">'
                    + SPIN + '<span>' + label + '</span></span>';
            }

            const lift = veil(opts.host, label);
            const restore = () => {
                lift();
                controlStates.forEach(({ control, disabled }) => { control.disabled = disabled; });
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-60', 'cursor-not-allowed');
                    btn.innerHTML = before;
                }
                if (guard) running.delete(guard);
            };

            try {
                const data = await work();
                if (data?.success) toast(data.success);
                if (opts.reload) {
                    setTimeout(() => location.reload(), opts.reloadDelay ?? 700);
                    return data;
                }
                restore();
                return data;
            } catch (err) {
                restore();
                if (opts.onError && opts.onError(err)) return null;
                toast(err.message, false);
                return null;
            }
        };

        const techniqueForm = document.getElementById('techniqueFormTitle')?.closest('section');
        const techniqueFormTitle = document.getElementById('techniqueFormTitle');
        const techniqueSubmit = document.getElementById('btnAddTechnique');
        const techniqueCancel = document.getElementById('btnCancelTechniqueEdit');

        const techniquePayload = () => {
            const maxRaw = document.getElementById('ntMax').value;

            return {
                name: document.getElementById('ntName').value.trim(),
                description: document.getElementById('ntDesc').value.trim() || null,
                // Ô số màu để trống nghĩa là KHÔNG giới hạn, không phải 0 màu.
                max_colors: maxRaw === '' ? null : parseInt(maxRaw, 10),
                min_dpi: parseInt(document.getElementById('ntDpi').value, 10) || 150,
                file_types: document.getElementById('ntFiles').value.trim() || 'png',
                lead_days: parseInt(document.getElementById('ntLead').value, 10) || 0,
                moq: parseInt(document.getElementById('ntMoq').value, 10) || 1,
                accepts_photo: document.getElementById('ntPhoto').checked,
                accepts_gradient: document.getElementById('ntGrad').checked,
                needs_underbase: document.getElementById('ntUnder').checked,
            };
        };

        const clearTechniqueForm = () => {
            techniqueForm?.removeAttribute('data-editing');
            if (techniqueFormTitle) techniqueFormTitle.textContent = 'Thêm kỹ thuật';
            if (techniqueSubmit) techniqueSubmit.textContent = 'Tạo kỹ thuật';
            if (techniqueCancel) techniqueCancel.hidden = true;
            document.getElementById('ntName').value = '';
            document.getElementById('ntDesc').value = '';
            document.getElementById('ntMax').value = '';
            document.getElementById('ntDpi').value = '150';
            document.getElementById('ntLead').value = '3';
            document.getElementById('ntMoq').value = '1';
            document.getElementById('ntFiles').value = 'png,pdf,svg';
            document.getElementById('ntPhoto').checked = true;
            document.getElementById('ntGrad').checked = true;
            document.getElementById('ntUnder').checked = true;
        };

        const editTechnique = card => {
            techniqueForm?.setAttribute('data-editing', card.dataset.technique);
            if (techniqueFormTitle) techniqueFormTitle.textContent = 'Sửa kỹ thuật';
            if (techniqueSubmit) techniqueSubmit.textContent = 'Lưu thay đổi';
            if (techniqueCancel) techniqueCancel.hidden = false;

            document.getElementById('ntName').value = card.dataset.techniqueName || '';
            document.getElementById('ntDesc').value = card.dataset.techniqueDescription || '';
            document.getElementById('ntMax').value = card.dataset.techniqueMaxColors || '';
            document.getElementById('ntDpi').value = card.dataset.techniqueMinDpi || '150';
            document.getElementById('ntLead').value = card.dataset.techniqueLeadDays || '3';
            document.getElementById('ntMoq').value = card.dataset.techniqueMoq || '1';
            document.getElementById('ntFiles').value = card.dataset.techniqueFileTypes || 'png';
            document.getElementById('ntPhoto').checked = card.dataset.techniqueAcceptsPhoto === '1';
            document.getElementById('ntGrad').checked = card.dataset.techniqueAcceptsGradient === '1';
            document.getElementById('ntUnder').checked = card.dataset.techniqueNeedsUnderbase === '1';

            techniqueForm?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('ntName').focus({ preventScroll: true });
        };

        // ── Bật/tắt, sửa và xoá kỹ thuật ─────────────────────────────
        document.querySelectorAll('[data-technique] [data-toggle]').forEach(cb => {
            cb.addEventListener('change', () => {
                const card = cb.closest('[data-technique]');
                const checked = cb.checked;

                withBusy(
                    {
                        host: card,
                        label: checked ? 'Đang bật...' : 'Đang tắt...',
                        // Trả nút gạt về đúng trạng thái thật của máy chủ.
                        onError: () => { cb.checked = !checked; return false; },
                    },
                    async () => {
                        const r = await post('/print/techniques/' + card.dataset.technique + '/toggle', { is_active: checked });
                        card.classList.toggle('opacity-60', !checked);
                        return r;
                    },
                );
            });
        });

        document.querySelectorAll('[data-technique-edit]').forEach(button => {
            button.addEventListener('click', () => editTechnique(button.closest('[data-technique]')));
        });

        document.querySelectorAll('[data-technique-delete]').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('[data-technique]');
                const name = button.dataset.techniqueName || card.dataset.techniqueName;
                if (!window.confirm('Xoá hẳn kỹ thuật "' + name + '"?\n\nChỉ kỹ thuật chưa có thiết kế khách sử dụng mới xoá được.')) return;

                withBusy(
                    { btn: button, host: card, label: 'Đang xoá...', reload: true },
                    () => del('/print/techniques/' + card.dataset.technique),
                );
            });
        });

        // ── Bậc khổ ──────────────────────────────────────────────────
        document.querySelectorAll('[data-tier]').forEach(row => {
            const nameInput = row.querySelector('[data-tier-name]');
            const widthInput = row.querySelector('[data-tier-w]');
            const heightInput = row.querySelector('[data-tier-h]');
            const editButton = row.querySelector('[data-tier-edit]');
            const saveButton = row.querySelector('[data-tier-save]');
            const cancelButton = row.querySelector('[data-tier-cancel]');

            const setEditing = editing => {
                [nameInput, widthInput, heightInput].forEach(input => { input.disabled = !editing; });
                editButton?.classList.toggle('hidden', editing);
                saveButton?.classList.toggle('hidden', !editing);
                cancelButton?.classList.toggle('hidden', !editing);
                row.dataset.editing = editing ? '1' : '0';
            };

            const tierControls = except =>
                Array.from(row.querySelectorAll('input, button')).filter(control => control !== except);

            editButton?.addEventListener('click', () => {
                row.dataset.originalName = nameInput.value;
                row.dataset.originalWidth = widthInput.value;
                row.dataset.originalHeight = heightInput.value;
                setEditing(true);
                nameInput.focus();
            });

            cancelButton?.addEventListener('click', () => {
                nameInput.value = row.dataset.originalName || nameInput.value;
                widthInput.value = row.dataset.originalWidth || widthInput.value;
                heightInput.value = row.dataset.originalHeight || heightInput.value;
                setEditing(false);
            });

            saveButton?.addEventListener('click', () => {
                const actionArea = saveButton.parentElement;

                withBusy(
                    {
                        btn: saveButton,
                        host: actionArea,
                        guard: row,
                        controls: tierControls(saveButton),
                        label: 'Đang lưu...',
                    },
                    async () => {
                        const r = await post('/print/tiers/' + row.dataset.tier, {
                            name: nameInput.value.trim(),
                            width_mm: parseInt(widthInput.value, 10) || 1,
                            height_mm: parseInt(heightInput.value, 10) || 1,
                        });
                        return r;
                    },
                ).then(result => {
                    if (!result) return;
                    row.querySelector('[data-tier-delete]').dataset.tierName = nameInput.value.trim();
                    setEditing(false);
                });
            });

            row.querySelector('[data-tier-toggle]')?.addEventListener('change', e => {
                const checked = e.target.checked;
                const switchArea = e.target.closest('label');

                withBusy(
                    {
                        host: switchArea,
                        guard: row,
                        controls: tierControls(e.target),
                        label: checked ? 'Đang bật...' : 'Đang tắt...',
                        onError: () => { e.target.checked = !checked; return false; },
                    },
                    () => post('/print/tiers/' + row.dataset.tier + '/toggle', { is_active: checked }),
                );
            });

            row.querySelector('[data-tier-delete]')?.addEventListener('click', e => {
                const deleteButton = e.currentTarget;
                if (!window.confirm('Xoá hẳn bậc khổ "' + (nameInput.value.trim() || deleteButton.dataset.tierName) + '"?\n\nCác ô giá nháp liên quan sẽ được xoá và quy tắc liên quan sẽ tắt.')) return;

                withBusy(
                    {
                        btn: deleteButton,
                        host: deleteButton.parentElement,
                        guard: row,
                        controls: tierControls(deleteButton),
                        label: 'Đang xoá...',
                        reload: true,
                    },
                    () => del('/print/tiers/' + row.dataset.tier),
                );
            });
        });

        document.getElementById('btnAddTier')?.addEventListener('click', e => {
            const addTierButton = e.currentTarget;
            const newTierForm = document.getElementById('newTierForm');
            const name = document.getElementById('newTierName').value.trim();
            if (!name) { document.getElementById('newTierName').focus(); return; }

            withBusy(
                { btn: addTierButton, host: newTierForm, guard: newTierForm, label: 'Đang thêm...', reload: true },
                () => post('{{ route('print.tiers.store') }}', {
                    name,
                    width_mm: parseInt(document.getElementById('newTierW').value, 10) || 1,
                    height_mm: parseInt(document.getElementById('newTierH').value, 10) || 1,
                }),
            );
        });

        // ── Tạo / lưu kỹ thuật ────────────────────────────────────────
        techniqueCancel?.addEventListener('click', clearTechniqueForm);

        techniqueSubmit?.addEventListener('click', () => {
            const values = techniquePayload();
            if (!values.name) { document.getElementById('ntName').focus(); return; }

            const editingId = techniqueForm?.dataset.editing;
            const url = editingId
                ? '/print/techniques/' + editingId
                : '{{ route('print.techniques.store') }}';

            withBusy(
                {
                    btn: techniqueSubmit,
                    host: techniqueForm,
                    guard: techniqueForm,
                    label: editingId ? 'Đang lưu...' : 'Đang tạo...',
                    reload: true,
                    reloadDelay: editingId ? 700 : 1200,
                },
                () => post(url, values),
            );
        });
    })();
    </script>
</x-app-layout>
