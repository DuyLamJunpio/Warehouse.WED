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
            không nối thì phôi đứng riêng với giá khai tay. Vị trí in <b>không phải khai</b>: bốn chỗ
            mặt trước, mặt sau, vai trái, vai phải luôn có sẵn, ở đây chỉ tick chỗ nào phôi này bán được.
            <b>Danh mục</b> dùng chung với hàng bán sẵn và là thứ dựng nên hàng nút lọc bên trang In áo —
            phôi để trống vẫn bày bán, chỉ là khách không lọc tới nó được.
        </p>
    </div>

    @include('print.partials.tabs')

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-5 items-start">
        <div class="space-y-4">
            @forelse ($blanks as $blank)
                <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden {{ $blank->is_active ? '' : 'opacity-60' }}"
                    data-blank="{{ $blank->id }}">

                    <div data-head class="relative flex flex-wrap items-center gap-3 px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" data-blank-toggle @checked($blank->is_active) class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <div class="flex-1 min-w-[180px]">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $blank->name }}</h2>
                            <p class="text-[11px] tabular-nums text-slate-500 dark:text-slate-400">
                                {{ number_format($blank->effectiveBasePrice(), 0, ',', '.') }} ₫ ·
                                {{ $blank->colors->count() }} màu ·
                                {{ count($blank->positionKeys()) }} vị trí in ·
                                {{ $blank->mockups->count() }} mockup ·
                                MOQ {{ $blank->moq }} · {{ $blank->lead_days }} ngày
                            </p>
                        </div>
                        @if ($blank->category)
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ $blank->category->name }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                                title="Phôi chưa xếp danh mục sẽ không lọc được bên web bán hàng.">
                                chưa xếp danh mục
                            </span>
                        @endif
                        @if ($blank->product_id)
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                nối kho · {{ $blank->product?->product_name }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">đứng riêng</span>
                        @endif
                        <button type="button" data-expand
                            class="px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Mở ra</button>
                        {{--
                            Xoá hẳn. Máy chủ chặn nếu còn thiết kế nào trỏ vào phôi —
                            nút này dành cho phôi khai nhầm, phôi ngừng bán thì gạt tắt.
                        --}}
                        <button type="button" data-blank-delete data-blank-name="{{ $blank->name }}" title="Xoá hẳn phôi này"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/70 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6" />
                            </svg>
                            <span>Xoá</span>
                        </button>
                    </div>

                    <div class="hidden" data-body>
                        {{-- Mockup theo màu --}}
                        <div data-mockups class="relative px-5 pb-5">
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
                                Tấm nào lệch khung quá 2% sẽ bị từ chối — hiệu chuẩn khung ảnh khai một lần cho cả
                                phôi, nên tấm lệch là mọi milimét khách kéo rơi sai chỗ trên đúng tấm đó.
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
                        Chưa có phôi in nào. Tạo phôi đầu tiên ở khung bên phải, rồi tải ảnh mockup lên.
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

        /** Câu lỗi đọc được: lỗi nghiệp vụ trước, rồi tới lỗi validate từng ô. */
        const errorText = (data, status) => {
            if (data && data.error) return data.error;
            if (data && data.errors) {
                const lines = Object.values(data.errors).flat();
                if (lines.length) return lines.join('\n');
            }
            return (data && data.message) || 'HTTP ' + status;
        };

        const request = async (url, method, body, isForm = false) => {
            const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
            if (body && !isForm) headers['Content-Type'] = 'application/json';

            const res = await fetch(url, {
                method,
                headers,
                body: body ? (isForm ? body : JSON.stringify(body)) : undefined,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw Object.assign(new Error(errorText(data, res.status)), { data });
            return data;
        };

        const post = (url, body, isForm = false) => request(url, 'POST', body, isForm);
        const del = (url) => request(url, 'DELETE');

        // ── Hiệu ứng chờ ─────────────────────────────────────────────
        const SPIN =
            '<svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none">'
            + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
            + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>';

        /** Vùng nào đang có thao tác chạy — chặn cú bấm thứ hai chồng lên. */
        const running = new WeakSet();

        /** Phủ mờ một vùng và hiện vòng quay lên trên nó. */
        const veil = (host, label) => {
            if (!host) return () => {};

            // Lớp phủ định vị tuyệt đối cần một mốc; vùng nào chưa có thì gắn tạm.
            const pinned = getComputedStyle(host).position === 'static';
            if (pinned) host.classList.add('relative');

            const el = document.createElement('div');
            el.className = 'absolute inset-0 z-30 flex items-center justify-center gap-2 rounded-[inherit] '
                + 'bg-white/70 dark:bg-slate-900/70 backdrop-blur-[1px] cursor-wait '
                + 'text-sm font-semibold text-slate-700 dark:text-slate-200';
            el.innerHTML = SPIN + (label ? '<span>' + label + '</span>' : '');
            host.appendChild(el);

            return () => {
                el.remove();
                if (pinned) host.classList.remove('relative');
            };
        };

        /**
         * Chạy một thao tác đụng cơ sở dữ liệu kèm hiệu ứng chờ.
         *
         * Ba lớp cùng lúc, vì thiếu lớp nào cũng để lại một lỗ:
         *   - nút bị khoá và đổi thành vòng quay: người bấm thấy máy đang chạy
         *   - vùng bị phủ mờ: mọi ô nhập bên trong ngừng nhận thao tác nửa chừng
         *   - cờ đang-chạy: chặn cú bấm thứ hai, thứ đẻ ra bản ghi trùng hoặc
         *     hai lượt xoá mà lượt sau báo "không tìm thấy"
         *
         * `reload` = xong thì trang sẽ tải lại, nên GIỮ hiệu ứng cho tới lúc đó.
         * Trả nút về bình thường rồi mới tải lại là một nhịp nháy khiến người
         * dùng tưởng đã xong và bấm thêm lần nữa.
         *
         * Lỗi thì hiệu ứng được gỡ và câu lỗi hiện thành toast — trừ khi
         * `onError` nhận lấy và tự xử lý.
         */
        const withBusy = async (opts, work) => {
            const guard = opts.host || opts.btn;
            if (guard && running.has(guard)) return null;
            if (guard) running.add(guard);

            const btn = opts.btn || null;
            const label = opts.label || '';
            const before = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                // Bọc trong một span để chạy được cả trong nút flex lẫn nút grid.
                btn.innerHTML = '<span class="inline-flex items-center justify-center gap-2">'
                    + SPIN + (label ? '<span>' + label + '</span>' : '') + '</span>';
            }

            const lift = veil(opts.host, label);
            const restore = () => {
                lift();
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-60', 'cursor-not-allowed');
                    btn.innerHTML = before;
                }
                if (guard) running.delete(guard);
            };

            try {
                const data = await work();
                if (data && data.success) toast(data.success);
                if (opts.reload) {
                    setTimeout(() => location.reload(), 700);
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
            const category = val('[data-f-category]');

            return {
                name: val('[data-f-name]'),
                description: val('[data-f-desc]') || null,
                // Chuỗi rỗng nghĩa là KHÔNG nối kho, khác hẳn với id 0.
                product_id: product === '' ? null : parseInt(product, 10),
                // Cũng vậy: rỗng = chưa xếp danh mục, không phải danh mục số 0.
                categories_id: category === '' ? null : parseInt(category, 10),
                base_price: parseInt(val('[data-f-price]'), 10) || 0,
                frame_width_mm: parseInt(val('[data-f-fw]'), 10) || 520,
                frame_height_mm: parseInt(val('[data-f-fh]'), 10) || 700,
                moq: parseInt(val('[data-f-moq]'), 10) || 1,
                lead_days: parseInt(val('[data-f-lead]'), 10) || 0,
                technique_ids: Array.from(form.querySelectorAll('[data-f-tech]:checked')).map(cb => parseInt(cb.value, 10)),
                positions: Array.from(form.querySelectorAll('[data-f-pos]:checked')).map(cb => cb.value),
                colors,
            };
        }

        // ── Biểu mẫu phôi ────────────────────────────────────────────
        document.querySelectorAll('[data-blank-form]').forEach(form => {
            const list = form.querySelector('[data-color-list]');

            /*
             * Bản mẫu chụp lúc trang vừa tải xong.
             *
             * Nhân bản dòng màu đang có trên màn hình là nhân luôn data-color-id
             * của nó, và nút xoá trên bản sao sẽ xoá đúng màu thật đó trong cơ sở
             * dữ liệu — một dòng trống trông vô hại lại gỡ mất màu đã lưu.
             */
            const template = list.querySelector('[data-color-row]').cloneNode(true);
            template.removeAttribute('data-color-id');
            template.querySelector('[data-color-name]').value = '';
            template.querySelector('[data-color-hex]').value = '#cccccc';
            template.querySelector('[data-color-tone]').value = '';

            form.querySelector('[data-color-add]')?.addEventListener('click', () => {
                list.appendChild(template.cloneNode(true));
            });

            /*
             * Xoá màu. Uỷ quyền sự kiện cho cả danh sách vì dòng thêm bằng nút
             * "+ Thêm màu" sinh ra sau khi trang đã gắn xong các trình xử lý.
             */
            list.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-color-del]');
                if (!btn) return;

                const row = btn.closest('[data-color-row]');
                const id = row.dataset.colorId;

                // Dòng chưa lưu thì chưa có gì dưới cơ sở dữ liệu để mà xoá.
                if (!id) {
                    row.remove();
                    return;
                }

                const name = row.querySelector('[data-color-name]').value.trim();
                const confirmed = confirm(
                    'Xoá hẳn màu "' + (name || 'này') + '" khỏi phôi?\n\n'
                    + 'Ảnh mockup chụp riêng cho màu này sẽ mất theo. Đơn cũ không ảnh hưởng — '
                    + 'hoá đơn lưu tên màu thành chữ.'
                );
                if (!confirmed) return;

                withBusy({ btn, host: row, reload: true }, () => del('/print/blank-colors/' + id));
            });

            form.querySelector('[data-blank-save]')?.addEventListener('click', (e) => {
                const id = form.dataset.blankForm;
                const url = id ? '/print/blanks/' + id : '{{ route('print.blanks.store') }}';

                withBusy(
                    { btn: e.currentTarget, host: form, label: 'Đang lưu...', reload: true },
                    () => post(url, collectBlank(form)),
                );
            });
        });

        // ── Mỗi thẻ phôi ─────────────────────────────────────────────
        document.querySelectorAll('[data-blank]').forEach(card => {
            const id = card.dataset.blank;

            card.querySelector('[data-expand]')?.addEventListener('click', (e) => {
                const body = card.querySelector('[data-body]');
                body.classList.toggle('hidden');
                e.currentTarget.textContent = body.classList.contains('hidden') ? 'Mở ra' : 'Thu lại';
            });

            const head = card.querySelector('[data-head]');

            card.querySelector('[data-blank-toggle]')?.addEventListener('change', async (e) => {
                const checked = e.target.checked;

                const r = await withBusy(
                    {
                        host: head,
                        label: checked ? 'Đang bật...' : 'Đang tắt...',
                        // Trả nút gạt về đúng trạng thái thật của máy chủ.
                        onError: () => { e.target.checked = !checked; return false; },
                    },
                    () => post('/print/blanks/' + id + '/toggle', { is_active: checked }),
                );

                if (r) card.classList.toggle('opacity-60', !checked);
            });

            card.querySelector('[data-blank-delete]')?.addEventListener('click', (e) => {
                const btn = e.currentTarget;
                const confirmed = confirm(
                    'Xoá hẳn phôi "' + btn.dataset.blankName + '"?\n\n'
                    + 'Mọi màu áo và ảnh mockup của phôi sẽ mất theo, không lấy lại được.\n'
                    + 'Chỉ muốn ngừng bán thì gạt tắt — đơn cũ giữ nguyên.'
                );
                if (!confirmed) return;

                withBusy({ btn, host: card, label: 'Đang xoá...', reload: true },
                    () => del('/print/blanks/' + id));
            });

            // ── Mockup ───────────────────────────────────────────────
            const gallery = card.querySelector('[data-mockups]');

            card.querySelector('[data-mockup-file]')?.addEventListener('change', (e) => {
                const input = e.target;
                const file = input.files && input.files[0];
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

                const upload = (force) => withBusy(
                    {
                        host: gallery,
                        label: 'Đang tải ảnh lên...',
                        reload: true,
                        onError: (err) => {
                            // Lệch khung là cảnh báo có thật, nhưng đôi khi không
                            // chụp lại được — hỏi rồi cho đi tiếp thay vì chặn cứng.
                            if (force || !err.data || !err.data.needs_confirm) return false;
                            if (!confirm(err.message + '\n\nVẫn dùng tấm này?')) return false;

                            upload(true);
                            return true;
                        },
                    },
                    () => send(force),
                );

                upload(false);
                // Trả ô chọn tệp về rỗng để chọn lại đúng tấm đó vẫn nổ sự kiện.
                input.value = '';
            });

            card.querySelectorAll('[data-mockup-delete]').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('Xoá tấm mockup này?')) return;

                    withBusy(
                        // Phủ cả ô ảnh chứ không riêng nút ×: nút quá nhỏ để nhìn
                        // ra vòng quay, còn ô ảnh thì thấy ngay tấm nào đang xoá.
                        { btn, host: btn.parentElement, reload: true },
                        () => del('/print/mockups/' + btn.dataset.mockupDelete),
                    );
                });
            });
        });
    })();
    </script>
</x-app-layout>
