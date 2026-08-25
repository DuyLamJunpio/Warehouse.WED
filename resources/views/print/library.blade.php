<x-app-layout>
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a></li>
                <li><span class="mx-1 text-slate-400">/</span><span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Thư viện</span></li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Thư viện sticker</h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
            Hình shop cung cấp sẵn cho khách kéo lên áo. Mỗi mục mang <b>ràng buộc riêng</b>, không chỉ là
            một tấm ảnh: giá, kỹ thuật nào dùng được, và giới hạn phóng to để khách không kéo ra vỡ nét.
        </p>
    </div>

    @include('print.partials.tabs')

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse ($assets as $asset)
                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-4 {{ $asset->is_active ? '' : 'opacity-60' }}"
                    data-asset="{{ $asset->id }}">
                    <div class="flex gap-3">
                        <div class="w-20 h-20 shrink-0 grid place-items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-2">
                            <img src="{{ Storage::url($asset->path) }}" alt="{{ $asset->name }}" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex-1 min-w-0 space-y-1.5">
                            <input type="text" data-a-name value="{{ $asset->name }}"
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5 font-semibold">
                            <div class="flex gap-1.5">
                                <input type="text" data-a-tag value="{{ $asset->tag }}" placeholder="Nhóm"
                                    class="w-1/2 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                                <input type="number" data-a-fee value="{{ $asset->fee }}" min="0" step="1000" title="Giá — 0 là miễn phí"
                                    class="w-1/2 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                            </div>
                            <div class="flex gap-1.5">
                                <input type="number" data-a-min value="{{ $asset->min_width_mm }}" min="1" title="Rộng nhỏ nhất (mm)"
                                    class="w-1/2 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                                <input type="number" data-a-max value="{{ $asset->max_width_mm }}" min="1" title="Rộng lớn nhất (mm)"
                                    class="w-1/2 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1">
                        @foreach ($techniques as $technique)
                            <label class="flex items-center gap-1.5 text-[11.5px] text-slate-600 dark:text-slate-300">
                                <input type="checkbox" data-a-tech value="{{ $technique->id }}"
                                    @checked(empty($asset->allowed_technique_ids) || in_array($technique->id, $asset->allowed_technique_ids))
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                                {{ $technique->name }}
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 font-mono">
                            {{ $asset->width_px }}×{{ $asset->height_px }} px
                        </span>
                        @if ($asset->has_alpha)
                            <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">nền trong suốt</span>
                        @else
                            <span class="px-2 py-1 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">nền ĐẶC</span>
                        @endif

                        <label class="relative inline-flex items-center cursor-pointer ml-auto">
                            <input type="checkbox" data-a-toggle @checked($asset->is_active) class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <button type="button" data-a-save class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lưu</button>
                    </div>
                </section>
            @empty
                <section class="sm:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-6">
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Thư viện đang trống. Khách vẫn tải file của họ lên được — thư viện chỉ là hàng có sẵn
                        để họ chọn nhanh.
                    </p>
                </section>
            @endforelse
        </div>

        <aside class="xl:sticky xl:top-6 space-y-5">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Phông chữ in được</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Khách chỉ chọn được trong danh sách này. Mỗi phông ở đây phải có tệp thật
                        <b>trong máy của xưởng</b> — không thì đơn nhận về sẽ không dàn nổi.
                    </p>
                </div>
                <div class="p-5 space-y-3">
                    @forelse ($fonts as $font)
                        <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 p-3 {{ $font->is_active ? '' : 'opacity-60' }}"
                            data-font="{{ $font->id }}">
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" data-f-toggle @checked($font->is_active) class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                            <div class="min-w-0 flex-1">
                                <input type="text" data-f-name value="{{ $font->name }}"
                                    data-family="{{ $font->family }}"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                <p class="mt-1 truncate text-[22px] leading-tight text-slate-800 dark:text-slate-100"
                                    style="font-family: {{ $font->family }}">Aa Bb &mdash; Áo lớp 12A1</p>
                                <p class="mt-0.5 text-[10.5px] font-mono text-slate-400 truncate">
                                    {{ $font->file_path ? 'tệp riêng · ' . $font->family : $font->family }}
                                </p>
                            </div>
                            <button type="button" data-f-save
                                class="shrink-0 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lưu</button>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Chưa có phông nào — khách sẽ không thấy nút thêm chữ trong studio.
                        </p>
                    @endforelse

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 space-y-2.5">
                        <input type="text" id="nfName" placeholder="Tên phông, VD: Chữ viết tay"
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        <input type="file" id="nfFile" accept=".woff2,.woff,.ttf,.otf"
                            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white dark:file:bg-slate-200 dark:file:text-slate-900">
                        <button type="button" id="btnAddFont"
                            class="w-full px-4 py-2 text-sm font-semibold text-white bg-slate-800 dark:bg-slate-200 dark:text-slate-900 rounded-lg hover:bg-slate-700 dark:hover:bg-white transition-colors">
                            Thêm phông
                        </button>
                        <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
                            Bỏ trống tệp thì dùng phông hệ thống (Arial, Georgia…). Tải tệp lên thì studio
                            hiện đúng mặt chữ đó — hệ thống tự đặt tên CSS, bạn không phải khai gì thêm.
                        </p>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Thêm sticker</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <label for="nName" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên</label>
                        <input type="text" id="nName" placeholder="VD: Ngôi sao viền"
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="nTag" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nhóm</label>
                            <input type="text" id="nTag" placeholder="Hình khối"
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                        <div>
                            <label for="nFee" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Giá (0 = free)</label>
                            <input type="number" id="nFee" value="0" min="0" step="1000"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="nMin" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng nhỏ nhất (mm)</label>
                            <input type="number" id="nMin" value="20" min="1"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                        <div>
                            <label for="nMax" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng lớn nhất (mm)</label>
                            <input type="number" id="nMax" value="250" min="1"
                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Kỹ thuật cho phép</span>
                        <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                            @foreach ($techniques as $technique)
                                <label class="flex items-center gap-1.5 text-[13px] text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" data-n-tech value="{{ $technique->id }}" checked
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                                    {{ $technique->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="nFile" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">File (PNG nền trong suốt hoặc SVG)</label>
                        <input type="file" id="nFile" accept=".png,.svg,.webp"
                            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white dark:file:bg-slate-200 dark:file:text-slate-900">
                    </div>
                    <button type="button" id="btnAddAsset"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        Thêm vào thư viện
                    </button>
                    <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-amber-500 pl-3">
                        PNG <b>không có nền trong suốt</b> sẽ in ra thành khối chữ nhật trên áo. Hệ thống kiểm
                        và cảnh báo, nhưng vẫn nhận — có lúc đó đúng là thứ bạn muốn.
                    </p>
                </div>
            </section>
        </aside>
    </div>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');

        const post = async (url, body, isForm = false) => {
            const res = await fetch(url, {
                method: 'POST',
                headers: Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    isForm ? {} : { 'Content-Type': 'application/json' }),
                body: isForm ? body : JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.error || data.message || 'HTTP ' + res.status);
            return data;
        };

        const checkedTechs = (root, selector) =>
            Array.from(root.querySelectorAll(selector + ':checked')).map(cb => parseInt(cb.value, 10));

        document.querySelectorAll('[data-asset]').forEach(card => {
            const id = card.dataset.asset;

            card.querySelector('[data-a-save]')?.addEventListener('click', async () => {
                try {
                    const r = await post('/print/library/' + id, {
                        name: card.querySelector('[data-a-name]').value.trim(),
                        tag: card.querySelector('[data-a-tag]').value.trim() || null,
                        fee: parseInt(card.querySelector('[data-a-fee]').value, 10) || 0,
                        min_width_mm: parseInt(card.querySelector('[data-a-min]').value, 10) || 1,
                        max_width_mm: parseInt(card.querySelector('[data-a-max]').value, 10) || 1,
                        technique_ids: checkedTechs(card, '[data-a-tech]'),
                    });
                    toast(r.success);
                } catch (err) { toast(err.message, false); }
            });

            card.querySelector('[data-a-toggle]')?.addEventListener('change', async (e) => {
                try {
                    const r = await post('/print/library/' + id + '/toggle', { is_active: e.target.checked });
                    card.classList.toggle('opacity-60', !e.target.checked);
                    toast(r.success);
                } catch (err) {
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                }
            });
        });

        // ── Phông chữ ────────────────────────────────────────────
        document.querySelectorAll('[data-font]').forEach(row => {
            const id = row.dataset.font;

            row.querySelector('[data-f-save]')?.addEventListener('click', async () => {
                try {
                    const r = await post('/print/fonts/' + id, {
                        name: row.querySelector('[data-f-name]').value.trim(),
                        // Phông có tệp riêng thì tên CSS do hệ thống giữ; gửi lại
                        // nguyên bản để máy chủ không phải đoán.
                        family: row.querySelector('[data-f-name]').dataset.family || 'sans-serif',
                    });
                    toast(r.success);
                } catch (err) { toast(err.message, false); }
            });

            row.querySelector('[data-f-toggle]')?.addEventListener('change', async (e) => {
                try {
                    const r = await post('/print/fonts/' + id + '/toggle', { is_active: e.target.checked });
                    row.classList.toggle('opacity-60', !e.target.checked);
                    toast(r.success);
                } catch (err) {
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                }
            });
        });

        document.getElementById('btnAddFont')?.addEventListener('click', async (e) => {
            const name = document.getElementById('nfName').value.trim();
            if (!name) { document.getElementById('nfName').focus(); return; }

            const fd = new FormData();
            fd.append('name', name);
            const file = document.getElementById('nfFile').files[0];
            // Không có tệp là dùng phông hệ thống — hợp lệ, không phải lỗi.
            if (file) fd.append('file', file);

            e.currentTarget.disabled = true;
            try {
                const r = await post('{{ route('print.fonts.store') }}', fd, true);
                toast(r.success);
                setTimeout(() => location.reload(), 1000);
            } catch (err) { toast(err.message, false); e.currentTarget.disabled = false; }
        });

        document.getElementById('btnAddAsset')?.addEventListener('click', async (e) => {
            const name = document.getElementById('nName').value.trim();
            const file = document.getElementById('nFile').files[0];

            if (!name) { document.getElementById('nName').focus(); return; }
            if (!file) { toast('Chưa chọn file.', false); return; }

            const fd = new FormData();
            fd.append('file', file);
            fd.append('name', name);
            fd.append('tag', document.getElementById('nTag').value.trim());
            fd.append('fee', document.getElementById('nFee').value || '0');
            fd.append('min_width_mm', document.getElementById('nMin').value || '1');
            fd.append('max_width_mm', document.getElementById('nMax').value || '1');
            checkedTechs(document, '[data-n-tech]').forEach(id => fd.append('technique_ids[]', id));

            e.currentTarget.disabled = true;
            try {
                const r = await post('{{ route('print.library.store') }}', fd, true);
                // Ảnh không có nền trong suốt vẫn được nhận, nhưng cảnh báo phải
                // ở lại đủ lâu để người dùng đọc kịp trước khi trang tải lại.
                toast(r.success, r.has_alpha !== false);
                setTimeout(() => location.reload(), r.has_alpha === false ? 3500 : 1000);
            } catch (err) { toast(err.message, false); e.currentTarget.disabled = false; }
        });
    })();
    </script>
</x-app-layout>
