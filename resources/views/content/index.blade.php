<x-app-layout>
    {{-- Header & Breadcrumb --}}
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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Nội dung Web bán lẻ</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý nội dung & Banner
                </h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Cấu hình Slide banner, dòng thông báo chạy, bộ sưu tập nổi bật và tiêu đề các khối trên website bán lẻ.
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-6 max-w-6xl">
        {{-- ══ 1. Slide hero ══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Slide ảnh đầu trang (Hero Banner)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ảnh/Video lớn chạy luân phiên ở đầu trang chủ</p>
                </div>
                <button type="button" id="btn-them-slide"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm slide mới</span>
                </button>
            </div>

            {{-- Recommendations Box --}}
            <div class="p-4 mx-5 mt-4 text-xs text-indigo-900 dark:text-indigo-200 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
                <p class="font-bold text-indigo-950 dark:text-indigo-300 mb-1">💡 Khuyến nghị kích thước & chất lượng banner</p>
                <ul class="space-y-0.5 list-disc list-inside text-[11px] text-slate-600 dark:text-slate-400">
                    <li><strong>Ảnh:</strong> khuyến nghị từ {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px, xuất 2400×1600 (3:2) hoặc 2560×1440 (16:9), dung lượng dưới {{ $limits['anh_mb'] }}MB.</li>
                    <li><strong>Bố cục:</strong> Giữ chủ thể người mẫu nằm ở 70–80% trung tâm khung hình để không bị che khi hiển thị trên mobile (4:5) và desktop (16:7).</li>
                    <li><strong>Video:</strong> khuyến nghị 1920×1080 (16:9), MP4 dưới {{ $limits['video_mb'] }}MB, lặp 5–10s kèm ảnh bìa tĩnh.</li>
                    <li class="font-semibold text-amber-700 dark:text-amber-300">Không đạt các mức trên vẫn được phép tải lên; hệ thống chỉ cảnh báo để tham khảo.</li>
                </ul>
            </div>

            <div id="danh-sach-slide" class="p-5 space-y-3">
                @forelse ($banners as $b)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-3.5 bg-slate-50/60 dark:bg-slate-700/30 border border-slate-200/70 dark:border-slate-700/70 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-slate-700/50"
                        data-id="{{ $b->id }}">
                        <div class="w-full sm:w-36 h-20 overflow-hidden bg-slate-200 dark:bg-slate-700 rounded-lg shrink-0 relative">
                            @if ($b->isVideo())
                                <video src="{{ Storage::url($b->media_path) }}" muted class="object-cover w-full h-full"></video>
                                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/70 text-[10px] text-white font-mono">Video</span>
                            @else
                                <img src="{{ Storage::url($b->media_path) }}" alt="{{ $b->alt }}" class="object-cover w-full h-full">
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ $b->heading ?: '(Không có tiêu đề)' }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ $b->subheading ?: '—' }}
                            </div>
                            <div class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                <span>{{ $b->isVideo() ? 'Định dạng: Video' : 'Định dạng: Ảnh' }}</span>
                                @if ($b->starts_at || $b->ends_at)
                                    <span>•</span>
                                    <span>Hiện từ {{ $b->starts_at?->format('d/m/Y H:i') ?: 'ngay' }} → {{ $b->ends_at?->format('d/m/Y H:i') ?: 'vô thời hạn' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 dark:border-slate-700/60">
                            <x-badge :variant="$b->is_live ? 'success' : 'neutral'" size="xs">
                                {{ $b->is_live ? 'Đang hiển thị' : 'Đang ẩn' }}
                            </x-badge>

                            <div class="inline-flex items-center gap-1">
                                <button type="button" data-id="{{ $b->id }}" data-direction="up"
                                    class="doi-thu-tu p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                                    title="Đẩy lên">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" data-id="{{ $b->id }}" data-direction="down"
                                    class="doi-thu-tu p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                                    title="Hạ xuống">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button type="button" data-slide="{{ $b->toJson() }}"
                                    class="sua-slide p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 rounded-lg transition-colors"
                                    title="Sửa slide">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" data-id="{{ $b->id }}" data-name="{{ $b->heading ?: 'slide này' }}"
                                    class="xoa-slide p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                                    title="Xoá slide">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-xs text-center text-slate-400">
                        Chưa có slide nào. Website đang dùng ảnh mặc định có sẵn trong hệ thống.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ══ 2. Chữ chạy nhỏ trên cùng ═══════════════════════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-1">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Dải chữ thông báo trên cùng (Top Banner)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Hiển thị ở đầu toàn bộ các trang trên web bán lẻ (thông báo freeship, quà tặng,...)</p>
                </div>
                <button type="button" id="btn-them-tb"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-600">
                    + Thêm dòng
                </button>
            </div>

            <div id="danh-sach-tb" class="space-y-2 mt-4">
                @foreach ($announcements as $a)
                    <div class="grid items-center grid-cols-12 gap-2 dong-tb">
                        <input type="text" value="{{ $a->value }}" maxlength="120"
                            placeholder="Ví dụ: Miễn phí giao hàng cho đơn từ 500.000 ₫"
                            class="tb-value col-span-12 sm:col-span-6 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <input type="datetime-local" value="{{ $a->starts_at?->format('Y-m-d\TH:i') }}"
                            class="tb-start col-span-5 sm:col-span-2.5 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <input type="datetime-local" value="{{ $a->ends_at?->format('Y-m-d\TH:i') }}"
                            class="tb-end col-span-5 sm:col-span-2.5 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <button type="button"
                            class="col-span-2 sm:col-span-1 px-2 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl xoa-dong-tb dark:hover:bg-rose-950/40">✕</button>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" id="btn-luu-tb"
                    class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    Lưu thông báo trên cùng
                </button>
            </div>
        </div>

        {{-- ══ 3. Bộ sưu tập ══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Bộ sưu tập nổi bật (Collections)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Mỗi bộ đang bật sẽ hiển thị thành một khối riêng trên trang chủ. Có thể tạo nhiều bộ theo mùa hoặc chủ đề.</p>
                </div>
                <button type="button" id="btn-them-bst"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tạo bộ sưu tập</span>
                </button>
            </div>

            <div class="space-y-3">
                @forelse ($collections as $bst)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 border border-slate-200/80 dark:border-slate-700/80 rounded-xl bg-slate-50/40 dark:bg-slate-700/20">
                        <div class="w-24 h-20 shrink-0 overflow-hidden rounded-lg bg-slate-200 dark:bg-slate-700">
                            @if ($bst->image_path)
                                <img src="{{ Storage::url($bst->image_path) }}" alt="{{ $bst->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center w-full h-full text-[10px] text-slate-400 text-center px-2">Chưa có ảnh đại diện</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $bst->title }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $bst->subtitle ?: '—' }}</div>
                            <div class="mt-1 text-[11px] text-slate-400 flex items-center gap-2">
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $bst->products->count() }} sản phẩm</span>
                                @if ($bst->starts_at || $bst->ends_at)
                                    <span>•</span>
                                    <span>{{ $bst->starts_at?->format('d/m/Y') ?: 'Ngay' }} → {{ $bst->ends_at?->format('d/m/Y') ?: 'Vô thời hạn' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 dark:border-slate-700/60">
                            <x-badge :variant="$bst->is_live ? 'success' : 'neutral'" size="xs">
                                {{ $bst->is_live ? 'Đang hiển thị' : 'Đang ẩn' }}
                            </x-badge>
                            <div class="flex gap-1.5 shrink-0">
                                <button type="button"
                                    data-bst="{{ $bst->only(['id','title','subtitle','cta_label','cta_link','status']) ? json_encode(array_merge($bst->only(['id','title','subtitle','cta_label','cta_link','status']), ['image_url'=>$bst->image_path ? Storage::url($bst->image_path) : null,'starts_at'=>$bst->starts_at?->format('Y-m-d\TH:i'),'ends_at'=>$bst->ends_at?->format('Y-m-d\TH:i'),'product_ids'=>$bst->products->pluck('id')]), JSON_UNESCAPED_UNICODE) : '{}' }}"
                                    class="sua-bst px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-xs dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600">Sửa</button>
                                <button type="button" data-id="{{ $bst->id }}" data-name="{{ $bst->title }}"
                                    class="xoa-bst px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg dark:bg-rose-950/40 dark:text-rose-400">Xoá</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-xs text-center text-slate-400">
                        Chưa có bộ sưu tập nào. Hãy tạo bộ mới, chọn ảnh đại diện và các sản phẩm muốn giới thiệu.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ══ 4. Tiêu đề các khối ════════════════════════════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs p-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tiêu đề các phân khu trang chủ</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Tùy chỉnh tiêu đề hiển thị các khối sản phẩm (Để trống sẽ sử dụng tiêu đề mặc định của hệ thống).
            </p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($headingLabels as $key => [$macDinh, $moTa])
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $moTa }}</label>
                        <input type="text" data-key="{{ $key }}" value="{{ $headings[$key] }}"
                            placeholder="{{ $macDinh }}" maxlength="255"
                            class="block w-full text-xs rounded-xl o-tieu-de bg-slate-50 border-slate-300 px-3.5 py-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <p class="mt-1 text-[11px] text-slate-400">Mặc định: {{ $macDinh }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-5">
                <button type="button" id="btn-luu-tieu-de"
                    class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    Lưu các tiêu đề
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM / SỬA SLIDE                                                  --}}
    {{-- ========================================================================= --}}
    <div id="drawer-slide"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col"
        tabindex="-1" aria-hidden="true">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <h3 id="tieu-de-drawer" class="text-base font-bold text-slate-900 dark:text-white">Thêm slide</h3>
            <button type="button" id="dong-drawer" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="form-slide" class="flex-1 flex flex-col justify-between" enctype="multipart/form-data">
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh hoặc Video <span class="text-rose-500">*</span></label>
                    <input type="file" name="media" id="slide-media"
                        accept="image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                    <p class="mt-1 text-[11px] text-slate-400">
                        Khuyến nghị: ảnh ≥ {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px, dưới {{ $limits['anh_mb'] }}MB · video MP4 dưới {{ $limits['video_mb'] }}MB. Không đạt vẫn được lưu.
                    </p>
                    <p id="canh-bao-anh" class="hidden mt-1 text-xs font-medium text-rose-600"></p>
                </div>

                <div id="vung-video" class="hidden space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh bìa video (Poster)</label>
                        <input type="file" name="poster" accept="image/*"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-100 dark:file:bg-slate-700 dark:file:text-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh riêng cho Mobile</label>
                        <input type="file" name="mobile" accept="image/*"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-100 dark:file:bg-slate-700 dark:file:text-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tiêu đề lớn</label>
                    <input type="text" name="heading" maxlength="255" placeholder="Ví dụ: Bộ Sưu Tập Tết 2027"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dòng mô tả phụ</label>
                    <textarea name="subheading" rows="2" maxlength="500" placeholder="Mô tả ưu đãi hoặc điểm nhấn..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Chữ trên nút CTA</label>
                        <input type="text" name="cta_label" maxlength="60" placeholder="Mua ngay"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label for="slide-cta-link-select" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nút sẽ đưa khách đến đâu?</label>
                        <select id="slide-cta-link-select"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <option value="">Không gắn liên kết</option>
                            <optgroup label="Trang chính">
                                <option value="/">Trang chủ</option>
                                <option value="/shop">Tất cả sản phẩm</option>
                                <option value="/shop?new=1">Hàng mới về</option>
                                <option value="/shop?sale=1">Sản phẩm đang giảm giá</option>
                                <option value="/shop?audience=Nam">Thời trang nam</option>
                                <option value="/shop?audience=N%E1%BB%AF">Thời trang nữ</option>
                                <option value="/shop?audience=Tr%E1%BA%BB%20em">Thời trang trẻ em</option>
                                <option value="/cart">Giỏ hàng</option>
                                <option value="/checkout">Thanh toán</option>
                                <option value="/in-ao">In áo theo yêu cầu</option>
                            </optgroup>
                            <optgroup label="Khu vực trên trang chủ">
                                <option value="/#new-arrivals">Khối hàng mới</option>
                                <option value="/#categories">Khối danh mục</option>
                                <option value="/#seasonal-drop">Khối bộ sưu tập</option>
                                <option value="/#newsletter">Khu vực đăng ký nhận tin</option>
                            </optgroup>
                            @if ($linkCategories->isNotEmpty())
                                <optgroup label="Danh mục sản phẩm">
                                    @foreach ($linkCategories as $category)
                                        <option value="/shop?category={{ rawurlencode($category->name) }}">
                                            Xem danh mục: {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($allProducts->isNotEmpty())
                                <optgroup label="Sản phẩm đang bán">
                                    @foreach ($allProducts as $product)
                                        @if ($product->slug)
                                            <option value="/products/{{ $product->slug }}">
                                                Xem sản phẩm: {{ $product->product_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endif
                            <option value="__custom__">Tự nhập đường dẫn khác...</option>
                        </select>
                        <input type="text" name="cta_link" id="slide-cta-link" maxlength="255"
                            placeholder="Chọn ở danh sách phía trên hoặc nhập /shop"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <p class="mt-1 text-[11px] text-slate-400">
                            Chọn một mục có sẵn để không cần nhớ URL. Nếu dẫn sang website khác, chọn “Tự nhập...” rồi nhập địa chỉ bắt đầu bằng <span class="font-mono">https://</span>.
                        </p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả ảnh (Alt SEO)</label>
                    <input type="text" name="alt" maxlength="255" placeholder="Người mẫu mặc áo khoác dáng dài"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bắt đầu hiện</label>
                        <input type="datetime-local" name="starts_at"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ngừng hiện</label>
                        <input type="datetime-local" name="ends_at"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <label class="flex items-center text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="status" value="1" checked
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2 dark:border-slate-600 dark:bg-slate-700">
                    Kích hoạt hiển thị slide này
                </label>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700" onclick="$('#dong-drawer').click()">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Lưu slide</button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM / SỬA BỘ SƯU TẬP                                            --}}
    {{-- ========================================================================= --}}
    <div id="drawer-bst"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-lg h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col"
        tabindex="-1" aria-hidden="true">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <h3 id="tieu-de-bst" class="text-base font-bold text-slate-900 dark:text-white">Tạo bộ sưu tập</h3>
            <button type="button" id="dong-drawer-bst" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="form-bst" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tên bộ sưu tập <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" maxlength="255" required placeholder="Ví dụ: BST Thu Đông 2026"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>
                <div>
                    <label for="bst-image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh đại diện <span id="bst-image-required-mark" class="text-rose-500">*</span></label>
                    <div id="bst-image-preview-wrap" class="hidden mb-2 h-36 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700">
                        <img id="bst-image-preview" src="" alt="Xem trước ảnh đại diện" class="w-full h-full object-cover">
                    </div>
                    <input type="file" name="image" id="bst-image" accept="image/jpeg,image/png,image/webp,image/avif" required
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                    <p class="mt-1 text-[11px] text-slate-400">Bắt buộc khi tạo bộ mới; khi sửa có thể bỏ qua để giữ ảnh cũ. Nhận JPG, PNG, WebP, AVIF, tối đa 5MB.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dòng mô tả phụ</label>
                    <textarea name="subtitle" rows="2" maxlength="500" placeholder="Thông điệp bộ sưu tập..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Chữ trên nút CTA</label>
                        <input type="text" name="cta_label" maxlength="60" placeholder="Khám phá ngay"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Đường dẫn đích (URL)</label>
                        <input type="text" name="cta_link" maxlength="255" placeholder="/shop"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bắt đầu hiện</label>
                        <input type="datetime-local" name="starts_at"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ngừng hiện</label>
                        <input type="datetime-local" name="ends_at"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <label class="flex items-center text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="status" checked
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2 dark:border-slate-600 dark:bg-slate-700">
                    Bật hiển thị bộ sưu tập này
                </label>

                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Chọn sản phẩm trong BST (<span id="so-da-chon" class="text-indigo-600 dark:text-indigo-400 font-bold">0</span> đã chọn)
                        </label>
                    </div>
                    <input type="text" id="loc-sp" placeholder="Lọc theo tên sản phẩm hoặc danh mục..."
                        class="block w-full mb-2 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <div class="p-2 overflow-y-auto border border-slate-200 rounded-xl max-h-60 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-750 custom-scrollbar">
                        @forelse ($allProducts as $sp)
                            <label
                                class="flex items-center gap-2.5 p-2 rounded-lg cursor-pointer dong-sp hover:bg-slate-100/70 dark:hover:bg-slate-700/60 transition-colors"
                                data-ten="{{ mb_strtolower($sp->product_name . ' ' . ($sp->category->name ?? '')) }}">
                                <input type="checkbox"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 chon-sp"
                                    value="{{ $sp->id }}">
                                <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ $sp->product_name }}</span>
                                <span class="ml-auto text-[11px] text-slate-400">{{ $sp->category->name ?? '—' }}</span>
                            </label>
                        @empty
                            <p class="p-3 text-xs text-slate-400 text-center">Chưa có sản phẩm nào để chọn.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700" onclick="$('#dong-drawer-bst').click()">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Lưu bộ sưu tập</button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            let idDangSua = null;
            const csrf = () => ({ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') });
            const moDrawer = () => window.openDrawer('drawer-slide');
            const dongDrawer = () => window.closeDrawer('drawer-slide');
            const slideCtaSelect = $('#slide-cta-link-select');
            const slideCtaInput = $('#slide-cta-link');
            let linkSlideDangChonSan = false;
            const capNhatLinkSlide = (value = '') => {
                const option = slideCtaSelect.find('option').filter(function() {
                    return this.value === value;
                }).first();

                slideCtaSelect.val(option.length ? value : (value ? '__custom__' : ''));
                slideCtaInput.val(value);
                linkSlideDangChonSan = option.length > 0;
            };

            slideCtaSelect.on('change', function() {
                const value = $(this).val();
                if (value === '__custom__') {
                    if (linkSlideDangChonSan) slideCtaInput.val('');
                    linkSlideDangChonSan = false;
                    slideCtaInput.focus();
                    return;
                }
                slideCtaInput.val(value || '');
                linkSlideDangChonSan = true;
            });

            $('#dong-drawer').click(dongDrawer);


            // ── Slide ────────────────────────────────────────────────
            $('#btn-them-slide').click(function() {
                idDangSua = null;
                $('#tieu-de-drawer').text('Thêm slide');
                $('#form-slide')[0].reset();
                capNhatLinkSlide();
                $('#vung-video, #canh-bao-anh').addClass('hidden');
                $('#slide-media').prop('required', true);
                moDrawer();
            });

            $(document).on('click', '.sua-slide', function() {
                const s = $(this).data('slide');
                idDangSua = s.id;
                $('#tieu-de-drawer').text('Sửa slide');
                $('#form-slide')[0].reset();

                const f = $('#form-slide');
                f.find('[name=heading]').val(s.heading || '');
                f.find('[name=subheading]').val(s.subheading || '');
                f.find('[name=cta_label]').val(s.cta_label || '');
                capNhatLinkSlide(s.cta_link || '');
                f.find('[name=alt]').val(s.alt || '');
                f.find('[name=starts_at]').val(s.starts_at ? s.starts_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=ends_at]').val(s.ends_at ? s.ends_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=status]').prop('checked', !!s.status);

                $('#slide-media').prop('required', false);
                $('#vung-video').toggleClass('hidden', s.media_type !== 'video');
                $('#canh-bao-anh').addClass('hidden');
                moDrawer();
            });

            $('#slide-media').on('change', function() {
                const file = this.files[0];
                const canhBao = $('#canh-bao-anh').addClass('hidden').text('');
                if (!file) return;

                const laVideo = file.type.startsWith('video/');
                $('#vung-video').toggleClass('hidden', !laVideo);

                const mbToiDa = laVideo ? {{ $limits['video_mb'] }} : {{ $limits['anh_mb'] }};
                const canhBaos = [];
                if (file.size > mbToiDa * 1024 * 1024) {
                    canhBaos.push(`File nặng ${(file.size / 1048576).toFixed(1)}MB, vượt mức khuyến nghị ${mbToiDa}MB; vẫn có thể lưu.`);
                }

                if (laVideo) {
                    canhBao.toggleClass('hidden', canhBaos.length === 0).text(canhBaos.join(' '));
                    return;
                }

                const img = new Image();
                img.onload = function() {
                    if (img.width < {{ $limits['anh_rong'] }} || img.height < {{ $limits['anh_cao'] }}) {
                        canhBaos.push(`Ảnh ${img.width}×${img.height}px, nhỏ hơn mức khuyến nghị {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px; vẫn có thể lưu.`);
                    }
                    canhBao.toggleClass('hidden', canhBaos.length === 0).text(canhBaos.join(' '));
                    URL.revokeObjectURL(img.src);
                };
                img.src = URL.createObjectURL(file);
            });

            $('#form-slide').submit(function(e) {
                e.preventDefault();
                const url = idDangSua ? '/content/banner/' + idDangSua : '/content/banner';
                window.submitFormWithProgress($(this), url, function(r) {
                    window.showToast(r.success);
                    setTimeout(() => location.reload(), 600);
                });
            });

            $(document).on('click', '.xoa-slide', function() {
                if (!confirm('Xác nhận xóa slide này?')) return;
                $.ajax({
                    url: '/content/banner/' + $(this).data('id'),
                    type: 'DELETE',
                    headers: csrf(),
                    success: function(r) {
                        window.showToast(r.success);
                        setTimeout(() => location.reload(), 600);
                    },
                    error: window.showAjaxError
                });
            });

            $(document).on('click', '.doi-thu-tu', function() {
                $.ajax({
                    url: '/content/banner/' + $(this).data('id') + '/reorder',
                    type: 'POST',
                    headers: csrf(),
                    data: { direction: $(this).data('direction') },
                    success: () => location.reload(),
                    error: window.showAjaxError
                });
            });

            // ── Chữ thông báo trên cùng ───────────────────────────────
            const dongTbMoi = () => $(`
                <div class="dong-tb grid grid-cols-12 gap-2 items-center">
                    <input type="text" maxlength="120" placeholder="Ví dụ: Miễn phí giao hàng cho đơn từ 500.000 ₫"
                        class="tb-value col-span-12 sm:col-span-6 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <input type="datetime-local"
                        class="tb-start col-span-5 sm:col-span-2.5 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <input type="datetime-local"
                        class="tb-end col-span-5 sm:col-span-2.5 text-xs rounded-xl bg-slate-50 border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <button type="button" class="col-span-2 sm:col-span-1 px-2 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl xoa-dong-tb dark:hover:bg-rose-950/40">✕</button>
                </div>`);

            $('#btn-them-tb').click(() => $('#danh-sach-tb').append(dongTbMoi()));
            $(document).on('click', '.xoa-dong-tb', function() { $(this).closest('.dong-tb').remove(); });

            $('#btn-luu-tb').click(function() {
                const items = [];
                $('#danh-sach-tb .dong-tb').each(function() {
                    const value = $(this).find('.tb-value').val().trim();
                    if (!value) return;
                    items.push({
                        value: value,
                        starts_at: $(this).find('.tb-start').val() || null,
                        ends_at: $(this).find('.tb-end').val() || null
                    });
                });

                $.ajax({
                    url: '{{ route('content.announcement') }}',
                    type: 'POST',
                    headers: csrf(),
                    // Top Banner có thể được lưu với danh sách rỗng khi người
                    // dùng xoá dòng cuối cùng, vì vậy không dùng form encoding.
                    contentType: 'application/json; charset=UTF-8',
                    processData: false,
                    data: JSON.stringify({ items: items }),
                    success: function(r) {
                        window.showToast(r.success);
                    },
                    error: window.showAjaxError
                });
            });

            // ── Bộ sưu tập ───────────────────────────────────────────
            let idBst = null;
            let bstPreviewUrl = null;
            const demDaChon = () => $('#so-da-chon').text($('.chon-sp:checked').length);
            const moBst = () => window.openDrawer('drawer-bst');
            $('#dong-drawer-bst').click(() => window.closeDrawer('drawer-bst'));
            $(document).on('change', '.chon-sp', demDaChon);

            const datAnhDaiDienBst = (src = '') => {
                if (bstPreviewUrl) {
                    URL.revokeObjectURL(bstPreviewUrl);
                    bstPreviewUrl = null;
                }
                $('#bst-image-preview').attr('src', src);
                $('#bst-image-preview-wrap').toggleClass('hidden', !src);
            };

            $('#bst-image').on('change', function() {
                const file = this.files?.[0];
                if (!file) {
                    datAnhDaiDienBst();
                    return;
                }
                const previewUrl = URL.createObjectURL(file);
                datAnhDaiDienBst(previewUrl);
                bstPreviewUrl = previewUrl;
            });


            $('#loc-sp').on('input', function() {
                const tu = $(this).val().trim().toLowerCase();
                $('.dong-sp').each(function() {
                    $(this).toggle(!tu || String($(this).data('ten')).includes(tu));
                });
            });

            $('#btn-them-bst').click(function() {
                idBst = null;
                $('#tieu-de-bst').text('Tạo bộ sưu tập');
                $('#form-bst')[0].reset();
                $('#bst-image').prop('required', true);
                $('#bst-image-required-mark').removeClass('hidden');
                datAnhDaiDienBst();
                $('.chon-sp').prop('checked', false);
                $('#loc-sp').val('').trigger('input');
                demDaChon();
                moBst();
            });

            $(document).on('click', '.sua-bst', function() {
                const b = $(this).data('bst');
                idBst = b.id;
                $('#tieu-de-bst').text('Sửa bộ sưu tập');
                $('#form-bst')[0].reset();

                const f = $('#form-bst');
                f.find('[name=title]').val(b.title || '');
                f.find('[name=subtitle]').val(b.subtitle || '');
                f.find('[name=cta_label]').val(b.cta_label || '');
                f.find('[name=cta_link]').val(b.cta_link || '');
                $('#bst-image').prop('required', false);
                $('#bst-image-required-mark').addClass('hidden');
                datAnhDaiDienBst(b.image_url || '');
                f.find('[name=starts_at]').val(b.starts_at || '');
                f.find('[name=ends_at]').val(b.ends_at || '');
                f.find('[name=status]').prop('checked', !!b.status);

                const daChon = (b.product_ids || []).map(String);
                $('.chon-sp').each(function() {
                    $(this).prop('checked', daChon.includes(String($(this).val())));
                });
                $('#loc-sp').val('').trigger('input');
                demDaChon();
                moBst();
            });

            $('#form-bst').submit(function(e) {
                e.preventDefault();
                const ids = $('.chon-sp:checked').map(function() { return $(this).val(); }).get();
                const formData = new FormData($('#form-bst')[0]);
                formData.set('status', $('#form-bst [name=status]').is(':checked') ? '1' : '0');
                ids.forEach((id) => formData.append('product_ids[]', id));

                $.ajax({
                    url: '/content/collection' + (idBst ? '/' + idBst : ''),
                    type: 'POST',
                    headers: csrf(),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(r) {
                        window.showToast(r.success);
                        setTimeout(() => location.reload(), 600);
                    },
                    error: window.showAjaxError
                });
            });

            $(document).on('click', '.xoa-bst', function() {
                if (!confirm('Xác nhận xóa bộ sưu tập "' + $(this).data('name') + '"?')) return;
                $.ajax({
                    url: '/content/collection/' + $(this).data('id'),
                    type: 'DELETE',
                    headers: csrf(),
                    success: function(r) {
                        window.showToast(r.success);
                        setTimeout(() => location.reload(), 600);
                    },
                    error: window.showAjaxError
                });
            });

            // ── Tiêu đề ──────────────────────────────────────────────
            $('#btn-luu-tieu-de').click(function() {
                const headings = {};
                $('.o-tieu-de').each(function() {
                    headings[$(this).data('key')] = $(this).val();
                });

                $.ajax({
                    url: '{{ route('content.headings') }}',
                    type: 'POST',
                    headers: csrf(),
                    data: { headings: headings },
                    success: function(r) {
                        window.showToast(r.success);
                    },
                    error: window.showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
