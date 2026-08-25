<x-app-layout>
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                </li>
                <li>
                    <span class="mx-1 text-slate-400">/</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">In áo · Phôi</span>
                </li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Phôi in</h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
            Nối vào sản phẩm trong kho là <b>tuỳ chọn</b> — có nối thì thừa hưởng giá và size từ biến thể,
            không nối thì phôi đứng riêng với giá khai tay. Vùng in khai bằng cách <b>kéo khung trên ảnh mockup</b>;
            milimét tính từ hiệu chuẩn khung ảnh.
        </p>
    </div>

    @include('print.partials.tabs')

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-5 items-start">
        <div class="space-y-4">
            @forelse ($blanks as $blank)
                @php($ref = $blank->mockups->first())
                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden {{ $blank->is_active ? '' : 'opacity-60' }}"
                    data-blank="{{ $blank->id }}">

                    <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" data-blank-toggle @checked($blank->is_active) class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <div class="flex-1 min-w-[180px]">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $blank->name }}</h2>
                            <p class="text-[11px] tabular-nums text-slate-500 dark:text-slate-400">
                                {{ number_format($blank->effectiveBasePrice(), 0, ',', '.') }} ₫ ·
                                {{ $blank->colors->count() }} màu ·
                                {{ $blank->zones->count() }} vùng in ·
                                {{ $blank->mockups->count() }} mockup ·
                                MOQ {{ $blank->moq }} · {{ $blank->lead_days }} ngày
                            </p>
                        </div>
                        @if ($blank->product_id)
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                nối kho · {{ $blank->product?->product_name }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">đứng riêng</span>
                        @endif
                        <button type="button" data-expand
                            class="px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Mở ra</button>
                    </div>

                    <div class="hidden" data-body>
                        {{-- Ảnh mockup + trình kéo khung --}}
                        <div class="p-5 grid grid-cols-1 lg:grid-cols-[minmax(0,380px)_minmax(0,1fr)] gap-5">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Khung vùng in</p>
                                <div class="relative select-none touch-none rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-50 dark:bg-slate-900/40"
                                    data-stage
                                    style="aspect-ratio: {{ $ref && $ref->height_px ? $ref->width_px . '/' . $ref->height_px : '400/460' }}; cursor: crosshair">
                                    @if ($ref)
                                        <img src="{{ Storage::url($ref->path) }}" alt="Mockup {{ $blank->name }}"
                                            class="absolute inset-0 w-full h-full object-contain pointer-events-none">
                                    @else
                                        <div class="absolute inset-0 grid place-items-center text-center px-6">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Chưa có mockup. Tải một tấm áo <b>trải phẳng, chính diện</b> lên trước
                                                rồi mới kéo khung vùng in.
                                            </p>
                                        </div>
                                    @endif

                                    @foreach ($blank->zones as $zone)
                                        <div class="absolute border border-dashed border-slate-400 rounded-sm pointer-events-none"
                                            style="left:{{ $zone->box_x }}%;top:{{ $zone->box_y }}%;width:{{ $zone->box_w }}%;height:{{ $zone->box_h }}%">
                                            <span class="absolute -top-2 left-1 px-1.5 rounded-full text-[9px] font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-500 whitespace-nowrap">{{ $zone->label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-center text-[11px] font-mono text-slate-500 dark:text-slate-400" data-note>
                                    Kéo chuột trên ảnh để vẽ một vùng in
                                </p>
                            </div>

                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Khung ảnh rộng (mm)</label>
                                        <input type="number" data-frame-w value="{{ $blank->frame_width_mm }}" min="50"
                                            class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Khung ảnh cao (mm)</label>
                                        <input type="number" data-frame-h value="{{ $blank->frame_height_mm }}" min="50"
                                            class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </div>
                                </div>
                                <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
                                    Hiệu chuẩn một lần: <b>chiếc áo này rộng bao nhiêu mm trong tấm ảnh</b>.
                                    Mọi vùng vẽ sau đó tự ra mm thật. Hai số này chỉ dùng để quy đổi lúc vẽ —
                                    muốn lưu lại thì sửa trong biểu mẫu phôi bên dưới.
                                </p>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên vùng</label>
                                        <input type="text" data-zone-label placeholder="VD: Ngực trái"
                                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Rộng mm</label>
                                            <input type="number" data-zone-w value="0" readonly
                                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Cao mm</label>
                                            <input type="number" data-zone-h value="0" readonly
                                                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" data-zone-save disabled
                                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    Lưu vùng in
                                </button>

                                @if ($blank->zones->isNotEmpty())
                                    <div class="pt-2 overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="text-left text-[10.5px] font-bold uppercase tracking-wider text-slate-400">
                                                    <th class="pb-1.5">Bật</th>
                                                    <th class="pb-1.5">Vùng</th>
                                                    <th class="pb-1.5 text-right">Rộng</th>
                                                    <th class="pb-1.5 text-right">Cao</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($blank->zones as $zone)
                                                    <tr class="border-t border-slate-100 dark:border-slate-700/60" data-zone="{{ $zone->id }}">
                                                        <td class="py-1.5">
                                                            <label class="relative inline-flex items-center cursor-pointer">
                                                                <input type="checkbox" data-zone-toggle @checked($zone->is_active) class="sr-only peer">
                                                                <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                                            </label>
                                                        </td>
                                                        <td class="py-1.5 text-slate-700 dark:text-slate-200">{{ $zone->label }}</td>
                                                        <td class="py-1.5 text-right font-mono tabular-nums text-slate-500">{{ $zone->width_mm }} mm</td>
                                                        <td class="py-1.5 text-right font-mono tabular-nums text-slate-500">{{ $zone->height_mm }} mm</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Mockup theo màu --}}
                        <div class="px-5 pb-5">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Ảnh mockup theo màu</p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @forelse ($blank->mockups as $mockup)
                                    <div class="relative w-20 h-24 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-50 dark:bg-slate-900/40">
                                        <img src="{{ Storage::url($mockup->path) }}" alt="" class="w-full h-full object-contain">
                                        <span class="absolute bottom-0 inset-x-0 px-1 py-0.5 text-[9px] text-center bg-white/85 dark:bg-slate-900/85 text-slate-600 dark:text-slate-300 truncate">
                                            {{ $blank->colors->firstWhere('id', $mockup->print_blank_color_id)?->name ?? $mockup->view }}
                                        </span>
                                        <button type="button" data-mockup-delete="{{ $mockup->id }}"
                                            class="absolute top-0.5 right-0.5 w-5 h-5 grid place-items-center rounded-full bg-rose-600 text-white text-xs leading-none hover:bg-rose-700"
                                            title="Xoá tấm này">&times;</button>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Chưa có tấm nào.</p>
                                @endforelse
                            </div>

                            <div class="flex flex-wrap items-end gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Màu</label>
                                    <select data-mockup-color class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        <option value="">— không gắn màu —</option>
                                        @foreach ($blank->colors as $color)
                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Góc</label>
                                    <select data-mockup-view class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                                        <option value="front">Mặt trước</option>
                                        <option value="back">Mặt sau</option>
                                        <option value="sleeve">Tay áo</option>
                                    </select>
                                </div>
                                <input type="file" data-mockup-file accept="image/*"
                                    class="text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white dark:file:bg-slate-200 dark:file:text-slate-900">
                            </div>
                            <p class="mt-2 text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-amber-500 pl-3">
                                Áo trải phẳng, chính diện, <b>cùng khoảng cách máy và cùng khung cắt cho mọi màu</b>.
                                Tấm nào lệch khung quá 2% sẽ bị từ chối — vùng in khai một lần cho cả phôi, tấm lệch
                                là khung in sai trên đúng tấm đó.
                            </p>
                        </div>

                        {{-- Thông tin phôi --}}
                        <div class="px-5 pb-5 pt-4 border-t border-slate-200/80 dark:border-slate-700/80">
                            @include('print.partials.blank-form', ['blank' => $blank, 'techniques' => $techniques, 'products' => $products])
                        </div>
                    </div>
                </section>
            @empty
                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-6">
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Chưa có phôi in nào. Tạo phôi đầu tiên ở khung bên phải, rồi tải mockup và khai vùng in.
                    </p>
                </section>
            @endforelse
        </div>

        {{-- Tạo phôi mới --}}
        <aside class="xl:sticky xl:top-6">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Thêm phôi</h2>
                </div>
                <div class="p-5">
                    @include('print.partials.blank-form', ['blank' => null, 'techniques' => $techniques, 'products' => $products])
                </div>
            </section>
        </aside>
    </div>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const toast = (msg, ok = true) => window.showToast(msg, ok ? 'success' : 'error');
        const clamp = (v, a, b) => Math.min(Math.max(v, a), b);

        const post = async (url, body, isForm = false) => {
            const res = await fetch(url, {
                method: 'POST',
                headers: Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    isForm ? {} : { 'Content-Type': 'application/json' }),
                body: isForm ? body : JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw Object.assign(new Error(data.error || data.message || 'HTTP ' + res.status), { data });
            return data;
        };

        /** Gom một biểu mẫu phôi — dùng chung cho khung tạo mới và khung sửa. */
        function collectBlank(form) {
            const val = sel => form.querySelector(sel)?.value ?? '';
            const colors = Array.from(form.querySelectorAll('[data-color-row]'))
                .map(row => ({
                    name: row.querySelector('[data-color-name]').value.trim(),
                    hex: row.querySelector('[data-color-hex]').value,
                    // Để trống thì máy chủ suy tông từ độ sáng; đó chỉ là gợi ý.
                    tone: row.querySelector('[data-color-tone]').value || null,
                }))
                .filter(c => c.name !== '');

            const product = val('[data-f-product]');

            return {
                name: val('[data-f-name]'),
                description: val('[data-f-desc]') || null,
                // Chuỗi rỗng nghĩa là KHÔNG nối kho, khác hẳn với id 0.
                product_id: product === '' ? null : parseInt(product, 10),
                base_price: parseInt(val('[data-f-price]'), 10) || 0,
                frame_width_mm: parseInt(val('[data-f-fw]'), 10) || 520,
                frame_height_mm: parseInt(val('[data-f-fh]'), 10) || 700,
                moq: parseInt(val('[data-f-moq]'), 10) || 1,
                lead_days: parseInt(val('[data-f-lead]'), 10) || 0,
                technique_ids: Array.from(form.querySelectorAll('[data-f-tech]:checked')).map(cb => parseInt(cb.value, 10)),
                colors,
            };
        }

        // ── Biểu mẫu phôi ────────────────────────────────────────────
        document.querySelectorAll('[data-blank-form]').forEach(form => {
            form.querySelector('[data-color-add]')?.addEventListener('click', () => {
                const list = form.querySelector('[data-color-list]');
                const row = list.querySelector('[data-color-row]').cloneNode(true);
                row.querySelector('[data-color-name]').value = '';
                row.querySelector('[data-color-hex]').value = '#cccccc';
                row.querySelector('[data-color-tone]').value = '';
                list.appendChild(row);
            });

            form.querySelector('[data-blank-save]')?.addEventListener('click', async (e) => {
                const id = form.dataset.blankForm;
                const btn = e.currentTarget;
                btn.disabled = true;
                try {
                    const url = id ? '/print/blanks/' + id : '{{ route('print.blanks.store') }}';
                    const r = await post(url, collectBlank(form));
                    toast(r.success);
                    setTimeout(() => location.reload(), 1000);
                } catch (err) { toast(err.message, false); btn.disabled = false; }
            });
        });

        // ── Mỗi thẻ phôi ─────────────────────────────────────────────
        document.querySelectorAll('[data-blank]').forEach(card => {
            const id = card.dataset.blank;
            const stage = card.querySelector('[data-stage]');
            const note = card.querySelector('[data-note]');
            const saveZoneBtn = card.querySelector('[data-zone-save]');
            let box = null, drawing = null, preview = null;

            card.querySelector('[data-expand]')?.addEventListener('click', (e) => {
                const body = card.querySelector('[data-body]');
                body.classList.toggle('hidden');
                e.currentTarget.textContent = body.classList.contains('hidden') ? 'Mở ra' : 'Thu lại';
            });

            card.querySelector('[data-blank-toggle]')?.addEventListener('change', async (e) => {
                try {
                    const r = await post('/print/blanks/' + id + '/toggle', { is_active: e.target.checked });
                    card.classList.toggle('opacity-60', !e.target.checked);
                    toast(r.success);
                } catch (err) {
                    // Trả nút về đúng trạng thái thật của máy chủ.
                    e.target.checked = !e.target.checked;
                    toast(err.message, false);
                }
            });

            // ── Kéo khung vùng in ────────────────────────────────────
            const frameW = () => parseInt(card.querySelector('[data-frame-w]').value, 10) || 520;
            const frameH = () => parseInt(card.querySelector('[data-frame-h]').value, 10) || 700;

            stage?.addEventListener('pointerdown', (e) => {
                const rect = stage.getBoundingClientRect();
                drawing = {
                    x: ((e.clientX - rect.left) / rect.width) * 100,
                    y: ((e.clientY - rect.top) / rect.height) * 100,
                    rect,
                };
                stage.setPointerCapture(e.pointerId);
                e.preventDefault();
            });

            stage?.addEventListener('pointermove', (e) => {
                if (!drawing) return;
                const r = drawing.rect;
                const x = clamp(((e.clientX - r.left) / r.width) * 100, 0, 100);
                const y = clamp(((e.clientY - r.top) / r.height) * 100, 0, 100);

                box = {
                    x: +Math.min(drawing.x, x).toFixed(2),
                    y: +Math.min(drawing.y, y).toFixed(2),
                    w: +Math.abs(x - drawing.x).toFixed(2),
                    h: +Math.abs(y - drawing.y).toFixed(2),
                };

                const wMm = Math.round((box.w / 100) * frameW());
                const hMm = Math.round((box.h / 100) * frameH());
                card.querySelector('[data-zone-w]').value = wMm;
                card.querySelector('[data-zone-h]').value = hMm;
                note.textContent = wMm + ' × ' + hMm + ' mm — hiệu chuẩn theo khung ' + frameW() + '×' + frameH() + ' mm';
                saveZoneBtn.disabled = !(wMm > 5 && hMm > 5);

                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'absolute border-2 border-indigo-500 rounded-sm pointer-events-none';
                    preview.style.background = 'rgba(99, 102, 241, 0.15)';
                    stage.appendChild(preview);
                }
                preview.style.left = box.x + '%';
                preview.style.top = box.y + '%';
                preview.style.width = box.w + '%';
                preview.style.height = box.h + '%';
            });

            stage?.addEventListener('pointerup', () => { drawing = null; });

            saveZoneBtn?.addEventListener('click', async (e) => {
                if (!box) return;
                e.currentTarget.disabled = true;
                try {
                    const r = await post('/print/blanks/' + id + '/zones', {
                        label: card.querySelector('[data-zone-label]').value.trim() || 'Vùng in',
                        width_mm: parseInt(card.querySelector('[data-zone-w]').value, 10),
                        height_mm: parseInt(card.querySelector('[data-zone-h]').value, 10),
                        box_x: box.x, box_y: box.y, box_w: box.w, box_h: box.h,
                    });
                    toast(r.success);
                    setTimeout(() => location.reload(), 900);
                } catch (err) { toast(err.message, false); e.currentTarget.disabled = false; }
            });

            card.querySelectorAll('[data-zone]').forEach(row => {
                row.querySelector('[data-zone-toggle]')?.addEventListener('change', async (e) => {
                    try {
                        const r = await post('/print/zones/' + row.dataset.zone + '/toggle', { is_active: e.target.checked });
                        toast(r.success);
                    } catch (err) { e.target.checked = !e.target.checked; toast(err.message, false); }
                });
            });

            // ── Mockup ───────────────────────────────────────────────
            card.querySelector('[data-mockup-file]')?.addEventListener('change', async (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const send = (force) => {
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('view', card.querySelector('[data-mockup-view]').value);
                    const colorId = card.querySelector('[data-mockup-color]').value;
                    if (colorId) fd.append('print_blank_color_id', colorId);
                    if (force) fd.append('force', '1');
                    return post('/print/blanks/' + id + '/mockups', fd, true);
                };

                try {
                    const r = await send(false);
                    toast(r.success);
                    setTimeout(() => location.reload(), 900);
                } catch (err) {
                    // Lệch khung là cảnh báo có thật, nhưng đôi khi không chụp lại
                    // được — hỏi rồi cho đi tiếp thay vì chặn cứng.
                    if (err.data && err.data.needs_confirm && confirm(err.message + '\n\nVẫn dùng tấm này?')) {
                        try {
                            const r2 = await send(true);
                            toast(r2.success);
                            setTimeout(() => location.reload(), 900);
                        } catch (e2) { toast(e2.message, false); }
                    } else {
                        toast(err.message, false);
                    }
                }
                e.target.value = '';
            });

            card.querySelectorAll('[data-mockup-delete]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Xoá tấm mockup này?')) return;
                    try {
                        const res = await fetch('/print/mockups/' + btn.dataset.mockupDelete, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.error || 'HTTP ' + res.status);
                        toast(data.success);
                        setTimeout(() => location.reload(), 700);
                    } catch (err) { toast(err.message, false); }
                });
            });
        });
    })();
    </script>
</x-app-layout>
