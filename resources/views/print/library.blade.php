<x-app-layout>
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a></li>
                <li><span class="mx-1 text-slate-400">/</span><span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Thư viện</span></li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Thư viện phông chữ &amp; sticker</h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
            Những thứ shop cung cấp sẵn cho khách trong studio. Phông chữ là lời hứa với xưởng — mỗi phông ở đây
            phải in được. Sticker mang <b>ràng buộc riêng</b>: giá, kỹ thuật dùng được, và giới hạn phóng to để
            khách không kéo ra vỡ nét.
        </p>
    </div>

    @include('print.partials.tabs')

    {{-- Phông tự tải lên mang tên CSS "print-font-{id}"; thiếu khai báo này thì ô xem trước hiện phông mặc định. --}}
    @if ($fontFaceCss !== '')
        <style>{!! $fontFaceCss !!}</style>
    @endif

    <div class="mb-4 inline-flex rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1" role="tablist">
        @foreach (['fonts' => ['Phông chữ', $fonts->count()], 'stickers' => ['Sticker', $assets->count()]] as $key => [$label, $count])
            <button type="button" role="tab" data-lib-tab="{{ $key }}"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 transition-colors
                       aria-selected:bg-slate-800 aria-selected:text-white dark:aria-selected:bg-slate-200 dark:aria-selected:text-slate-900">
                {{ $label }}
                <span class="px-1.5 rounded-md text-[11px] tabular-nums bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    {{-- ── Bảng phông chữ ───────────────────────────────────────────── --}}
    <section data-lib-panel="fonts" role="tabpanel"
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bảng phông chữ</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                Khách chỉ chọn được trong bảng này. Phông tự tải lên thì studio hiện đúng mặt chữ, và nhân viên tải
                lại được tệp gốc để gửi xưởng. Bỏ trống tệp là dùng phông hệ thống (Arial, Georgia…).
            </p>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] gap-2.5 items-center">
                <input type="text" id="nfName" placeholder="Tên phông, VD: Chữ viết tay"
                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                <input type="file" id="nfFile" accept=".woff2,.woff,.ttf,.otf"
                    class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white dark:file:bg-slate-200 dark:file:text-slate-900">
                <button type="button" id="btnAddFont"
                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                    Thêm phông
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200/80 dark:border-slate-700/80">
                        <th class="px-5 py-2.5 w-16">Bật</th>
                        <th class="px-5 py-2.5">Tên phông</th>
                        <th class="px-5 py-2.5">Xem trước</th>
                        <th class="px-5 py-2.5">Tệp</th>
                        <th class="px-5 py-2.5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fonts as $font)
                        <tr data-font="{{ $font->id }}"
                            class="border-b border-slate-100 dark:border-slate-700/60 last:border-0 {{ $font->is_active ? '' : 'opacity-60' }}">
                            <td class="px-5 py-3">
                                <label class="relative inline-flex items-center cursor-pointer" title="Bật/tắt trong studio">
                                    <input type="checkbox" data-f-toggle @checked($font->is_active) class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>
                            <td class="px-5 py-3 min-w-[220px]">
                                <input type="text" data-f-name value="{{ $font->name }}" data-family="{{ $font->family }}"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                            </td>
                            <td class="px-5 py-3 min-w-[240px]">
                                <p class="truncate text-[22px] leading-tight text-slate-800 dark:text-slate-100"
                                    style="font-family: {{ $font->family }}">Aa Bb &mdash; Áo lớp 12A1</p>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                @if ($font->file_path)
                                    <span class="px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 font-mono text-[10.5px] uppercase">
                                        .{{ pathinfo($font->file_path, PATHINFO_EXTENSION) }}
                                    </span>
                                    <span class="ml-1 text-[11px] text-slate-400">tệp riêng</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 text-[10.5px]">phông hệ thống</span>
                                    <span class="block mt-1 font-mono text-[10.5px] text-slate-400">{{ $font->family }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if ($font->file_path)
                                    <a href="{{ route('print.fonts.download', $font) }}"
                                        class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline mr-3">Tải phông</a>
                                @endif
                                <button type="button" data-f-save
                                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lưu</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                Chưa có phông nào — khách sẽ không thấy nút thêm chữ trong studio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ── Bảng sticker ─────────────────────────────────────────────── --}}
    <section data-lib-panel="stickers" role="tabpanel" hidden
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bảng sticker</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                Hình shop cung cấp sẵn cho khách kéo lên áo. Khách vẫn tải file của họ lên được — thư viện chỉ là
                hàng có sẵn để họ chọn nhanh. Tắt thay vì xoá: thiết kế cũ vẫn đang trỏ vào sticker.
            </p>

            <div class="mt-3 grid grid-cols-2 md:grid-cols-6 gap-2.5">
                <div class="col-span-2">
                    <label for="nName" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên</label>
                    <input type="text" id="nName" placeholder="VD: Ngôi sao viền"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                </div>
                <div>
                    <label for="nTag" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Nhóm</label>
                    <input type="text" id="nTag" placeholder="Hình khối"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                </div>
                <div>
                    <label for="nFee" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Giá (0 = free)</label>
                    <input type="number" id="nFee" value="0" min="0" step="1000"
                        class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                </div>
                <div>
                    <label for="nMin" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng min (mm)</label>
                    <input type="number" id="nMin" value="20" min="1"
                        class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                </div>
                <div>
                    <label for="nMax" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng max (mm)</label>
                    <input type="number" id="nMax" value="250" min="1"
                        class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
                </div>
                <div class="col-span-2 md:col-span-3">
                    <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Kỹ thuật cho phép</span>
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
                <div class="col-span-2 md:col-span-2">
                    <label for="nFile" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">File (PNG nền trong suốt hoặc SVG)</label>
                    <input type="file" id="nFile" accept=".png,.svg,.webp"
                        class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white dark:file:bg-slate-200 dark:file:text-slate-900">
                </div>
                <div class="col-span-2 md:col-span-1 flex items-end">
                    <button type="button" id="btnAddAsset"
                        class="w-full px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                        Thêm sticker
                    </button>
                </div>
            </div>
            <p class="mt-2.5 text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-amber-500 pl-3">
                PNG <b>không có nền trong suốt</b> sẽ in ra thành khối chữ nhật trên áo. Hệ thống kiểm và cảnh báo,
                nhưng vẫn nhận — có lúc đó đúng là thứ bạn muốn.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200/80 dark:border-slate-700/80">
                        <th class="px-4 py-2.5">Ảnh</th>
                        <th class="px-4 py-2.5">Tên · Nhóm</th>
                        <th class="px-4 py-2.5 text-right">Giá (₫)</th>
                        <th class="px-4 py-2.5 text-right">Rộng min–max (mm)</th>
                        <th class="px-4 py-2.5">Kỹ thuật cho phép</th>
                        <th class="px-4 py-2.5">Thông số</th>
                        <th class="px-4 py-2.5">Bật</th>
                        <th class="px-4 py-2.5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr data-asset="{{ $asset->id }}"
                            class="border-b border-slate-100 dark:border-slate-700/60 last:border-0 align-top {{ $asset->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3">
                                <div class="w-14 h-14 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-1.5">
                                    <img src="{{ Storage::url($asset->path) }}" alt="{{ $asset->name }}" class="max-w-full max-h-full object-contain">
                                </div>
                            </td>
                            <td class="px-4 py-3 min-w-[200px] space-y-1.5">
                                <input type="text" data-a-name value="{{ $asset->name }}"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5 font-semibold">
                                <input type="text" data-a-tag value="{{ $asset->tag }}" placeholder="Nhóm"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" data-a-fee value="{{ $asset->fee }}" min="0" step="1000" title="Giá — 0 là miễn phí"
                                    class="w-28 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <input type="number" data-a-min value="{{ $asset->min_width_mm }}" min="1" title="Rộng nhỏ nhất (mm)"
                                        class="w-20 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                                    <span class="text-slate-400">–</span>
                                    <input type="number" data-a-max value="{{ $asset->max_width_mm }}" min="1" title="Rộng lớn nhất (mm)"
                                        class="w-20 text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-xs py-1.5">
                                </div>
                            </td>
                            <td class="px-4 py-3 min-w-[160px]">
                                <div class="flex flex-col gap-1">
                                    @foreach ($techniques as $technique)
                                        <label class="flex items-center gap-1.5 text-[11.5px] text-slate-600 dark:text-slate-300">
                                            <input type="checkbox" data-a-tech value="{{ $technique->id }}"
                                                @checked(empty($asset->allowed_technique_ids) || in_array($technique->id, $asset->allowed_technique_ids))
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                                            {{ $technique->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap space-y-1 text-[11px]">
                                <span class="block font-mono text-slate-600 dark:text-slate-300">{{ $asset->width_px }}×{{ $asset->height_px }} px</span>
                                @if ($asset->has_alpha)
                                    <span class="inline-block px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">nền trong suốt</span>
                                @else
                                    <span class="inline-block px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">nền ĐẶC</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <label class="relative inline-flex items-center cursor-pointer" title="Bật/tắt trong studio">
                                    <input type="checkbox" data-a-toggle @checked($asset->is_active) class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ Storage::url($asset->path) }}" target="_blank" rel="noopener noreferrer"
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline mr-3">File gốc</a>
                                <button type="button" data-a-save class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lưu</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                Thư viện đang trống. Khách vẫn tải file của họ lên được — thư viện chỉ là hàng có sẵn
                                để họ chọn nhanh.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');

        // ── Tab: nhớ theo #hash để tải lại trang (sau khi thêm mới) vẫn ở đúng bảng.
        const tabs = document.querySelectorAll('[data-lib-tab]');
        const showTab = (key) => {
            tabs.forEach(tab => tab.setAttribute('aria-selected', String(tab.dataset.libTab === key)));
            document.querySelectorAll('[data-lib-panel]').forEach(panel => {
                panel.hidden = panel.dataset.libPanel !== key;
            });
        };
        tabs.forEach(tab => tab.addEventListener('click', () => {
            history.replaceState(null, '', '#' + tab.dataset.libTab);
            showTab(tab.dataset.libTab);
        }));
        showTab(location.hash === '#stickers' ? 'stickers' : 'fonts');

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
        const SPIN = '<svg class="inline-block h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 0-4 4h4a4 4 0 0 1 4-4V0a8 8 0 0 0-4 4z"></path></svg>';
        const busy = (button, label) => {
            const original = button.innerHTML;
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-wait');
            button.innerHTML = '<span class="inline-flex items-center gap-2">' + SPIN + '<span>' + label + '</span></span>';
            return () => {
                button.disabled = false;
                button.classList.remove('opacity-60', 'cursor-wait');
                button.innerHTML = original;
            };
        };

        const checkedTechs = (root, selector) =>
            Array.from(root.querySelectorAll(selector + ':checked')).map(cb => parseInt(cb.value, 10));

        document.querySelectorAll('[data-asset]').forEach(row => {
            const id = row.dataset.asset;

            row.querySelector('[data-a-save]')?.addEventListener('click', async (event) => {
                const restore = busy(event.currentTarget, 'Đang lưu...');
                try {
                    const r = await post('/print/library/' + id, {
                        name: row.querySelector('[data-a-name]').value.trim(),
                        tag: row.querySelector('[data-a-tag]').value.trim() || null,
                        fee: parseInt(row.querySelector('[data-a-fee]').value, 10) || 0,
                        min_width_mm: parseInt(row.querySelector('[data-a-min]').value, 10) || 1,
                        max_width_mm: parseInt(row.querySelector('[data-a-max]').value, 10) || 1,
                        technique_ids: checkedTechs(row, '[data-a-tech]'),
                    });
                    toast(r.success);
                } catch (err) { toast(err.message, false); }
                finally { restore(); }
            });

            row.querySelector('[data-a-toggle]')?.addEventListener('change', async (e) => {
                e.target.disabled = true;
                e.target.classList.add('animate-pulse');
                try {
                    const r = await post('/print/library/' + id + '/toggle', { is_active: e.target.checked });
                    row.classList.toggle('opacity-60', !e.target.checked);
                    toast(r.success);
                } catch (err) {
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                } finally {
                    e.target.disabled = false;
                    e.target.classList.remove('animate-pulse');
                }
            });
        });

        // ── Phông chữ ────────────────────────────────────────────
        document.querySelectorAll('[data-font]').forEach(row => {
            const id = row.dataset.font;

            row.querySelector('[data-f-save]')?.addEventListener('click', async (event) => {
                const restore = busy(event.currentTarget, 'Đang lưu...');
                try {
                    const r = await post('/print/fonts/' + id, {
                        name: row.querySelector('[data-f-name]').value.trim(),
                        // Phông có tệp riêng thì tên CSS do hệ thống giữ; gửi lại
                        // nguyên bản để máy chủ không phải đoán.
                        family: row.querySelector('[data-f-name]').dataset.family || 'sans-serif',
                    });
                    toast(r.success);
                } catch (err) { toast(err.message, false); }
                finally { restore(); }
            });

            row.querySelector('[data-f-toggle]')?.addEventListener('change', async (e) => {
                e.target.disabled = true;
                e.target.classList.add('animate-pulse');
                try {
                    const r = await post('/print/fonts/' + id + '/toggle', { is_active: e.target.checked });
                    row.classList.toggle('opacity-60', !e.target.checked);
                    toast(r.success);
                } catch (err) {
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                } finally {
                    e.target.disabled = false;
                    e.target.classList.remove('animate-pulse');
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

            const restore = busy(e.currentTarget, 'Đang tải lên...');
            try {
                const r = await post('{{ route('print.fonts.store') }}', fd, true);
                toast(r.success);
                setTimeout(() => location.reload(), 1000);
            } catch (err) { toast(err.message, false); restore(); }
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

            const restore = busy(e.currentTarget, 'Đang tải lên...');
            try {
                const r = await post('{{ route('print.library.store') }}', fd, true);
                // Ảnh không có nền trong suốt vẫn được nhận, nhưng cảnh báo phải
                // ở lại đủ lâu để người dùng đọc kịp trước khi trang tải lại.
                toast(r.success, r.has_alpha !== false);
                setTimeout(() => location.reload(), r.has_alpha === false ? 3500 : 1000);
            } catch (err) { toast(err.message, false); restore(); }
        });
    })();
    </script>
</x-app-layout>
