<x-app-layout>
    @php
        $color = $design->blank?->colors->firstWhere('name', $design->color_name);
        $mockup = $design->blank?->mockups->firstWhere('print_blank_color_id', $color?->id)
            ?? $design->blank?->mockups->first();
        $zones = $design->blank?->zones->keyBy('key') ?? collect();
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('print.designs') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Duyệt thiết kế</a></li>
                    <li><span class="mx-1 text-slate-400">/</span><span class="text-slate-800 dark:text-slate-200 font-medium">{{ $design->code }}</span></li>
                </ol>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                {{ $design->code }}
            </h1>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $design->blank?->name }} · {{ $design->color_name }} · size {{ $design->size }} ·
                {{ $design->technique?->name }} · {{ $design->qty }} áo
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            @include('print.partials.design-status', ['design' => $design])
            <a href="{{ route('print.designs.svg', $design) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-slate-800 dark:bg-slate-200 dark:text-slate-900 rounded-xl hover:bg-slate-700 dark:hover:bg-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                Tải file cho xưởng (.svg)
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)] gap-5 items-start">
        {{-- Ảnh ghép để đối chiếu --}}
        <div class="space-y-4">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Ảnh ghép để đối chiếu</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Đây là bản dựng lại từ toạ độ mm, không phải file in. Thợ nhận file gốc kèm bảng toạ độ bên phải.
                    </p>
                </div>
                <div class="p-5">
                    <div class="relative mx-auto w-full max-w-[360px] rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-50 dark:bg-slate-900/40"
                        style="aspect-ratio: {{ $mockup && $mockup->height_px ? $mockup->width_px . '/' . $mockup->height_px : '400/460' }}">
                        @if ($mockup)
                            <img src="{{ Storage::url($mockup->path) }}" alt="" class="absolute inset-0 w-full h-full object-contain">
                        @endif

                        @foreach ($zones as $zone)
                            <div class="absolute border border-dashed border-slate-400/70 rounded-sm"
                                style="left:{{ $zone->box_x }}%;top:{{ $zone->box_y }}%;width:{{ $zone->box_w }}%;height:{{ $zone->box_h }}%">
                                @foreach (($design->placements ?? []) as $p)
                                    @continue(($p['zone'] ?? null) !== $zone->key)
                                    <img src="{{ $p['asset_url'] ?? '' }}" alt=""
                                        class="absolute object-contain"
                                        style="left:{{ ($p['x_mm'] / max($zone->width_mm, 1)) * 100 }}%;
                                               top:{{ ($p['y_mm'] / max($zone->height_mm, 1)) * 100 }}%;
                                               width:{{ ($p['w_mm'] / max($zone->width_mm, 1)) * 100 }}%;
                                               height:{{ ($p['h_mm'] / max($zone->height_mm, 1)) * 100 }}%;
                                               transform: rotate({{ $p['rotation'] ?? 0 }}deg)">
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    @if ($boxes)
                        <dl class="mt-4 space-y-1 text-xs">
                            @foreach ($boxes as $key => $box)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-slate-400">Khung bao {{ $zones[$key]->label ?? $key }}</dt>
                                    <dd class="font-mono tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $box['width_mm'] }} × {{ $box['height_mm'] }} mm
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            </section>

            {{-- Đơn hàng gắn với thiết kế này --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Đơn hàng</h2>
                @if ($invoice)
                    <dl class="space-y-1.5 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Mã đơn</dt>
                            <dd><a href="{{ route('order.show', $invoice->id) }}" class="font-mono font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ $invoice->order_code }}</a></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Khách</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ $invoice->shipping_name }} · {{ $invoice->shipping_phone }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Đã thanh toán</dt>
                            <dd>
                                @if ((int) $invoice->pay_status === 1)
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rồi</span>
                                @else
                                    <span class="font-semibold text-amber-600 dark:text-amber-400">Chưa</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Khách đã chốt thiết kế nhưng <b>chưa đặt hàng</b>. Chưa có tiền nào được thu.
                    </p>
                @endif
            </section>
        </div>

        {{-- Bảng toạ độ + giá + quyết định --}}
        <div class="space-y-5">
            {{-- Bảng toạ độ giao cho thợ in --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bảng toạ độ cho thợ in</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Gửi thợ <b>file .svg</b> ở nút trên cùng — nó giữ đúng toạ độ mm, ảnh gốc theo link và
                        chữ ở dạng <b>text thật</b> (mở bằng Illustrator hoặc Corel, convert to outlines là in được).
                        Bảng dưới đây và ảnh ghép bên trái là để đối chiếu bằng mắt.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200/80 dark:border-slate-700/80">
                                <th class="px-5 py-2.5">Vùng</th>
                                <th class="px-5 py-2.5">Hình</th>
                                <th class="px-5 py-2.5 text-right">Vị trí X/Y</th>
                                <th class="px-5 py-2.5 text-right">Khổ</th>
                                <th class="px-5 py-2.5 text-right">Xoay</th>
                                <th class="px-5 py-2.5 text-right">DPI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sheet as $row)
                                @php($low = $row['dpi'] !== null && $design->technique && $row['dpi'] < $design->technique->min_dpi)
                                <tr class="border-b border-slate-100 dark:border-slate-700/60 last:border-0">
                                    <td class="px-5 py-2.5 text-slate-700 dark:text-slate-200">
                                        {{ $row['zone'] }}
                                        <span class="block text-[10.5px] font-mono text-slate-400">{{ $row['zone_size_mm'] }}</span>
                                    </td>
                                    <td class="px-5 py-2.5">
                                        @if ($row['asset_url'])
                                            <a href="{{ $row['asset_url'] }}" target="_blank" rel="noopener noreferrer"
                                                class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $row['asset_name'] ?? 'Hình' }}</a>
                                        @else
                                            <span class="text-slate-500">{{ $row['asset_name'] ?? 'Hình' }}</span>
                                        @endif
                                        <span class="block text-[10.5px] font-mono text-slate-400">{{ $row['source_px'] }} px</span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                        {{ $row['x_mm'] }} / {{ $row['y_mm'] }} mm
                                    </td>
                                    <td class="px-5 py-2.5 text-right font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                        {{ $row['width_mm'] }} × {{ $row['height_mm'] }} mm
                                    </td>
                                    <td class="px-5 py-2.5 text-right font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                        {{ $row['rotation'] }}°
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <span class="font-mono tabular-nums font-semibold {{ $low ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $row['dpi'] ?? '—' }}
                                        </span>
                                        @if ($low)
                                            <span class="block text-[10px] text-rose-500">dưới {{ $design->technique->min_dpi }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            {{-- Bảng kê giá đã đóng băng --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bảng kê giá đã chốt</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Tính theo bảng giá <b>#{{ $design->pricing_version_id ?? '—' }}</b>
                        @if ($design->pricingVersion?->published_at)
                            xuất bản {{ $design->pricingVersion->published_at->format('d/m/Y H:i') }}.
                        @endif
                        Sửa bảng giá sau đó không đổi con số ở đây.
                    </p>
                </div>
                <div class="p-5">
                    @forelse (($design->price_breakdown ?? []) as $line)
                        <div class="flex items-baseline justify-between gap-3 py-1.5 border-b border-dashed border-slate-100 dark:border-slate-700/60 last:border-0 {{ !empty($line['sub']) ? 'pl-4' : '' }}">
                            <span class="{{ !empty($line['sub']) ? 'text-xs text-slate-500 dark:text-slate-400' : 'text-[13px] text-slate-700 dark:text-slate-200' }}">
                                @if (!empty($line['sub']))<span class="text-indigo-500">&#8627;</span>@endif
                                {{ $line['label'] }}
                                @if (!empty($line['meta']))
                                    <span class="block text-[10.5px] text-slate-400">{{ $line['meta'] }}</span>
                                @endif
                            </span>
                            <span class="font-mono text-[13px] tabular-nums {{ $line['amount'] < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $line['amount'] < 0 ? '−' : '' }}{{ number_format(abs($line['amount']), 0, ',', '.') }} ₫
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400">Không có bảng kê.</p>
                    @endforelse

                    <div class="mt-4 pt-3 border-t-2 border-slate-800 dark:border-slate-200 flex items-baseline justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mỗi áo</span>
                        <span class="font-mono text-lg font-bold tabular-nums text-slate-900 dark:text-white">
                            {{ number_format($design->unit_price, 0, ',', '.') }} ₫
                        </span>
                    </div>
                    <div class="mt-1 flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Tổng × {{ $design->qty }}</span>
                        <span class="font-mono text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-200">
                            {{ number_format($design->total_price, 0, ',', '.') }} ₫
                        </span>
                    </div>
                </div>
            </section>
            {{-- Quyết định --}}
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Quyết định</h2>

                @if ($design->reviewed_at)
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        Đã xử lý {{ $design->reviewed_at->format('d/m/Y H:i') }}
                        @if ($design->reviewer) bởi {{ $design->reviewer->name }} @endif
                        @if ($design->review_note)
                            <span class="block mt-1 text-slate-700 dark:text-slate-200">Lý do: {{ $design->review_note }}</span>
                        @endif
                    </p>
                @endif

                <div class="mt-3">
                    <label for="reviewNote" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                        Lý do từ chối — bắt buộc khi từ chối, khách sẽ đọc đúng câu này
                    </label>
                    <textarea id="reviewNote" rows="2"
                        placeholder="VD: File 800x600px, in khổ A4 chỉ được 70 DPI nên sẽ rỗ. Nhờ bạn gửi file lớn hơn."
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">{{ $design->review_note }}</textarea>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-decision="approved"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors">
                        Duyệt — đưa vào xưởng
                    </button>
                    <button type="button" data-decision="rejected"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors">
                        Từ chối
                    </button>
                </div>

                <p class="mt-3 text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-amber-500 pl-3">
                    Từ chối một đơn <b>đã thu tiền</b> thì nhớ hoàn tiền cho khách — hệ thống không tự làm việc đó.
                </p>
            </section>
        </div>
    </div>

    <script>
    (() => {
        "use strict";

        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        document.querySelectorAll('[data-decision]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const decision = btn.dataset.decision;
                const note = document.getElementById('reviewNote').value.trim();

                // Từ chối mà không nói lý do thì khách không sửa được gì.
                if (decision === 'rejected' && !note) {
                    window.showToast('Nhập lý do từ chối để khách biết phải sửa gì.', 'error');
                    document.getElementById('reviewNote').focus();
                    return;
                }

                btn.disabled = true;
                try {
                    const res = await fetch('{{ route('print.designs.review', $design) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ decision: decision, note: note || null }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.error || data.message || 'HTTP ' + res.status);

                    window.showToast(data.success, 'success');
                    setTimeout(() => location.reload(), 1200);
                } catch (err) {
                    window.showToast(err.message, 'error');
                    btn.disabled = false;
                }
            });
        });
    })();
    </script>
</x-app-layout>
