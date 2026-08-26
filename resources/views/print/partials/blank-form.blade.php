{{--
    Biểu mẫu phôi in, dùng chung cho hai chỗ: khung "Thêm phôi" bên phải và
    phần sửa bên trong mỗi thẻ phôi. `$blank` rỗng nghĩa là đang tạo mới.

    JavaScript đọc các thuộc tính data-f-* nên đổi tên chúng là phải sửa cả
    hàm collectBlank() bên print/blanks.blade.php.
--}}
@php($colors = $blank?->colors->where('is_active', true)->values() ?? collect())
@php($positions = \App\Services\PrintPositions::payload())
@php($enabled = $blank?->positionKeys() ?? \App\Services\PrintPositions::keys())

<div data-blank-form="{{ $blank?->id }}" class="space-y-3">
    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên phôi</label>
        <input type="text" data-f-name value="{{ $blank?->name }}" placeholder="VD: Áo thun cotton 100%"
            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
    </div>

    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Mô tả cho khách</label>
        <textarea data-f-desc rows="2" placeholder="Chất liệu, form dáng — một hai câu."
            class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">{{ $blank?->description }}</textarea>
    </div>

    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Nối vào sản phẩm trong kho</label>
        <select data-f-product class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
            <option value="">— không nối, phôi đứng riêng —</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($blank?->product_id === $product->id)>
                    {{ $product->product_name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-400">
            Nối rồi thì giá và size lấy từ biến thể thật, ô giá bên dưới bị bỏ qua.
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Giá phôi (đồng)</label>
            <input type="number" data-f-price min="0" step="1000" value="{{ $blank?->base_price ?? 0 }}"
                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">MOQ</label>
                <input type="number" data-f-moq min="1" value="{{ $blank?->moq ?? 1 }}"
                    class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Ngày làm</label>
                <input type="number" data-f-lead min="0" value="{{ $blank?->lead_days ?? 3 }}"
                    class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Khung ảnh rộng (mm)</label>
            <input type="number" data-f-fw min="50" value="{{ $blank?->frame_width_mm ?? 520 }}"
                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Khung ảnh cao (mm)</label>
            <input type="number" data-f-fh min="50" value="{{ $blank?->frame_height_mm ?? 700 }}"
                class="w-full text-right tabular-nums rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm">
        </div>
    </div>

    <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
        Hiệu chuẩn một lần: <b>chiếc áo trong tấm mockup rộng và cao bao nhiêu milimét thật</b>.
        Mọi kích thước khách kéo bên web quy ra mm từ hai số này — sai ở đây là sai cả xưởng.
    </p>

    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Vị trí in bán được</label>
        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
            @foreach ($positions as $position)
                <label class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                    <input type="checkbox" data-f-pos value="{{ $position['key'] }}"
                        @checked(in_array($position['key'], $enabled, true))
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                    <span>
                        {{ $position['label'] }}
                        <span class="block text-[10.5px] font-mono text-slate-400 tabular-nums">
                            tối đa {{ $position['max_width_mm'] }}×{{ $position['max_height_mm'] }} mm
                        </span>
                    </span>
                </label>
            @endforeach
        </div>
        <p class="mt-1 text-[11px] text-slate-400">
            Bốn chỗ này là hằng số trong mã nguồn, không phải khung in để kéo. Bỏ tick chỗ nào phôi này
            không in được — áo ba lỗ không có vai, áo khoác không in lưng. Trần milimét là giới hạn của
            xưởng; bên trong nó khách đặt hình ở đâu và to nhỏ thế nào là tuỳ khách.
        </p>
    </div>

    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Kỹ thuật in được</label>
        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
            @forelse ($techniques as $technique)
                <label class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                    <input type="checkbox" data-f-tech value="{{ $technique->id }}"
                        @checked($blank && $blank->techniques->contains('id', $technique->id))
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                    {{ $technique->name }}
                </label>
            @empty
                <p class="text-[11.5px] text-amber-600 dark:text-amber-400">
                    Chưa có kỹ thuật nào đang bật — tạo ở tab "Kỹ thuật in".
                </p>
            @endforelse
        </div>
    </div>

    <div>
        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Màu áo</label>
        <div class="space-y-1.5" data-color-list>
            @foreach ($colors->isEmpty() ? [null] : $colors as $color)
                <div class="flex items-center gap-2" data-color-row>
                    <input type="color" data-color-hex value="{{ $color->hex ?? '#cccccc' }}"
                        class="w-9 h-9 shrink-0 rounded-lg border border-slate-300 dark:border-slate-600 bg-transparent cursor-pointer p-0.5">
                    <input type="text" data-color-name value="{{ $color->name ?? '' }}" placeholder="Tên màu"
                        class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                    <select data-color-tone class="shrink-0 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900/60 text-sm py-1.5">
                        <option value="">tự suy</option>
                        <option value="light" @selected(($color->tone ?? null) === 'light')>sáng</option>
                        <option value="dark" @selected(($color->tone ?? null) === 'dark')>tối</option>
                    </select>
                </div>
            @endforeach
        </div>
        <button type="button" data-color-add
            class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">+ Thêm màu</button>
        <p class="mt-1 text-[11px] text-slate-400">
            Tông quyết định phụ phí lót trắng. Để "tự suy" thì hệ thống đoán theo độ sáng —
            xám mélange nằm giữa nên nhớ soi lại.
        </p>
    </div>

    <button type="button" data-blank-save
        class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
        {{ $blank ? 'Lưu phôi' : 'Tạo phôi' }}
    </button>

    @unless ($blank)
        <p class="text-[11.5px] leading-relaxed text-slate-500 dark:text-slate-400 border-l-2 border-indigo-500 pl-3">
            Tạo xong thì mở thẻ phôi ra và <b>tải ảnh mockup</b> lên — áo trải phẳng, chính diện.
            Chưa có mockup thì studio bên web chưa dựng được màn hình cho khách.
        </p>
    @endunless
</div>
