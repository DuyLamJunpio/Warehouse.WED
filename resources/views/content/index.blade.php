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
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ảnh/Video lớn chạy luân phiên ở đầu trang chủ · Kéo thẻ để đổi thứ tự</p>
                </div>
                <button type="button" id="btn-them-slide" {{ $banners->count() >= 3 ? 'disabled' : '' }}
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm slide ({{ $banners->count() }}/3)</span>
                </button>
            </div>

            {{-- Recommendations Box --}}
            <div class="p-4 mx-5 mt-4 text-xs text-indigo-900 dark:text-indigo-200 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
                <p class="font-bold text-indigo-950 dark:text-indigo-300 mb-1">💡 Khuyến nghị kích thước & chất lượng banner</p>
                <ul class="space-y-0.5 list-disc list-inside text-[11px] text-slate-600 dark:text-slate-400">
                    <li><strong>Ảnh:</strong> khuyến nghị từ {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px, xuất 2400×1600 (3:2) hoặc 2560×1440 (16:9).</li>
                    <li><strong>Chống cắt chủ thể:</strong> tải media dọc riêng cho mobile và chọn <em>trọng tâm hiển thị</em> đúng nơi có người mẫu/sản phẩm.</li>
                    <li><strong>Video:</strong> khuyến nghị 1920×1080 (16:9), lặp 5–10s kèm ảnh bìa tĩnh.</li>
                    <li class="font-semibold text-amber-700 dark:text-amber-300">Hero không giới hạn dung lượng ở ứng dụng; vẫn nên tối ưu file để website tải nhanh.</li>
                </ul>
            </div>

            <div id="danh-sach-slide" class="p-5 space-y-3">
                @forelse ($banners as $b)
                    <div class="slide-card flex flex-col sm:flex-row sm:items-center gap-4 p-3.5 bg-slate-50/60 dark:bg-slate-700/30 border border-slate-200/70 dark:border-slate-700/70 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-grab active:cursor-grabbing"
                        data-id="{{ $b->id }}" data-media-url="{{ Storage::url($b->media_path) }}"
                        data-poster-url="{{ $b->poster_path ? Storage::url($b->poster_path) : '' }}" draggable="true">
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
                                <button type="button" data-id="{{ $b->id }}" data-status="{{ $b->status ? '1' : '0' }}"
                                    class="bat-tat-slide p-1.5 {{ $b->status ? 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 dark:hover:text-white dark:hover:bg-slate-700' }} rounded-lg transition-colors"
                                    title="{{ $b->status ? 'Tạm ẩn slide' : 'Bật hiển thị slide' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
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
                                <button type="button" data-id="{{ $b->id }}"
                                    class="nhan-ban-slide p-1.5 text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-lg transition-colors"
                                    title="Nhân bản thành bản nháp">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h8a1 1 0 011 1v10a1 1 0 01-1 1h-4m-5 3H4a1 1 0 01-1-1V8a1 1 0 011-1h8a1 1 0 011 1v8a1 1 0 01-1 1H8z"/></svg>
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
                        Không giới hạn dung lượng tại ứng dụng. Khuyến nghị: ảnh ≥ {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px · video MP4/WebM 1920×1080 để tải nhanh hơn.
                    </p>
                    <p id="canh-bao-anh" class="hidden mt-1 text-xs font-medium text-rose-600"></p>
                </div>

                <div id="slide-media-preview" class="hidden overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700">
                    <img id="slide-preview-image" src="" alt="Xem trước banner" class="hidden w-full h-40 object-cover">
                    <video id="slide-preview-video" class="hidden w-full h-40 object-cover" controls muted playsinline></video>
                    <p id="slide-preview-note" class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400"></p>
                </div>

                <div id="vung-video" class="hidden">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh bìa video (Poster)</label>
                        <input type="file" name="poster" accept="image/*"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-100 dark:file:bg-slate-700 dark:file:text-slate-300">
                        <p class="mt-1 text-[11px] text-slate-400">Dùng làm ảnh chờ khi video đang tải.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh riêng cho Mobile</label>
                    <input type="file" name="mobile" accept="image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-100 dark:file:bg-slate-700 dark:file:text-slate-300">
                    <p class="mt-1 text-[11px] text-slate-400">Hoàn toàn độc lập với desktop. Dùng media dọc 4:5 hoặc 9:16; nếu bỏ trống, website tự dùng media desktop.</p>
                </div>

                <section class="space-y-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <div class="flex items-center justify-between gap-3">
                        <div><h4 class="text-xs font-bold text-slate-800 dark:text-white">Nội dung trên Hero</h4><p class="text-[11px] text-slate-500">Thêm nhiều cặp tiêu đề và nội dung cho slide này.</p></div>
                        <button type="button" id="them-khoi-noi-dung" class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">+ Thêm nội dung</button>
                    </div>
                    <div id="danh-sach-khoi-noi-dung" class="space-y-2"></div>
                </section>
                <section class="space-y-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <div class="flex items-center justify-between gap-3">
                        <div><h4 class="text-xs font-bold text-slate-800 dark:text-white">Nút CTA</h4><p class="text-[11px] text-slate-500">Mỗi Hero có thể có nhiều nút. Dùng dấu − để bớt nút.</p></div>
                        <button type="button" id="them-cta-slide" class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">+ Thêm CTA</button>
                    </div>
                    <div id="danh-sach-cta-slide" class="space-y-2"></div>
                </section>
                <section class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white">Vị trí nội dung trên Hero</h4>
                    <p class="mt-0.5 text-[11px] text-slate-500">Thiết lập độc lập cho website màn rộng và mobile.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach (['desktop_layout' => 'Desktop', 'mobile_layout' => 'Mobile'] as $prefix => $device)
                            <div class="space-y-2"><p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $device }}</p><div class="grid grid-cols-3 gap-2">
                                <label class="text-[11px] text-slate-500">Ngang<select name="{{ $prefix }}[horizontal]" class="mt-1 block w-full rounded-lg border-slate-300 py-1.5 text-xs dark:bg-slate-700 dark:border-slate-600"><option value="left">Trái</option><option value="center">Giữa</option><option value="right">Phải</option></select></label>
                                <label class="text-[11px] text-slate-500">Dọc<select name="{{ $prefix }}[vertical]" class="mt-1 block w-full rounded-lg border-slate-300 py-1.5 text-xs dark:bg-slate-700 dark:border-slate-600"><option value="top">Trên</option><option value="center">Giữa</option><option value="bottom">Dưới</option></select></label>
                                <label class="text-[11px] text-slate-500">Chữ<select name="{{ $prefix }}[text_align]" class="mt-1 block w-full rounded-lg border-slate-300 py-1.5 text-xs dark:bg-slate-700 dark:border-slate-600"><option value="left">Trái</option><option value="center">Giữa</option><option value="right">Phải</option></select></label>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2"><label class="text-[11px] text-slate-500">Trọng tâm media<select name="{{ $prefix }}[focal_point]" class="mt-1 block w-full rounded-lg border-slate-300 py-1.5 text-xs dark:bg-slate-700 dark:border-slate-600"><option value="top-left">Trên trái</option><option value="top">Trên</option><option value="top-right">Trên phải</option><option value="left">Trái</option><option value="center">Giữa</option><option value="right">Phải</option><option value="bottom-left">Dưới trái</option><option value="bottom">Dưới</option><option value="bottom-right">Dưới phải</option></select></label><label class="text-[11px] text-slate-500">Lớp phủ tối <span data-overlay-value="{{ $prefix }}">35%</span><input type="range" name="{{ $prefix }}[overlay_opacity]" min="0" max="90" value="35" class="mt-2 w-full accent-indigo-600"></label></div></div>
                        @endforeach
                    </div>
                </section>
                <section class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <div class="flex items-center justify-between"><div><h4 class="text-xs font-bold text-slate-800 dark:text-white">Preview bố cục</h4><p class="text-[11px] text-slate-500">Xem vị trí khối chữ và lớp phủ trên Desktop/Mobile.</p></div><div class="inline-flex rounded-lg bg-slate-100 p-1 text-[11px] dark:bg-slate-700"><button type="button" data-hero-preview="desktop" class="hero-preview-mode rounded-md bg-white px-2 py-1 font-semibold text-slate-700 shadow-sm dark:bg-slate-600 dark:text-white">Desktop</button><button type="button" data-hero-preview="mobile" class="hero-preview-mode rounded-md px-2 py-1 text-slate-500 dark:text-slate-300">Mobile</button></div></div>
                    <div id="hero-layout-preview" class="relative mt-3 flex min-h-36 overflow-hidden rounded-lg bg-slate-700 p-4 text-white transition-all"><div class="absolute inset-0 bg-black/35" data-hero-overlay></div><div class="relative z-10 max-w-[85%]" data-hero-preview-content><p class="text-lg font-bold">Tiêu đề Hero</p><p class="mt-1 text-xs opacity-90">Đoạn mô tả sẽ hiển thị ở đây.</p><span class="mt-3 inline-block rounded-md bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">CTA</span></div></div>
                </section>
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
                <div class="flex flex-wrap gap-2 -mt-1">
                    <button type="button" class="lich-slide px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-950/50 dark:text-indigo-300" data-preset="now">Hiện ngay</button>
                    <button type="button" class="lich-slide px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg dark:bg-slate-700 dark:text-slate-300" data-preset="week">Kết thúc sau 7 ngày</button>
                    <button type="button" class="lich-slide px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg dark:bg-slate-700 dark:text-slate-300" data-preset="clear">Bỏ lịch hẹn</button>
                </div>

                <label class="flex items-center text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="status" value="0">
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
            let slidePreviewUrl = null;
            const datXemTruocSlide = (url = '', mediaType = 'image', note = '', poster = '', laFileTam = false) => {
                if (slidePreviewUrl) {
                    URL.revokeObjectURL(slidePreviewUrl);
                    slidePreviewUrl = null;
                }

                const wrap = $('#slide-media-preview');
                const image = $('#slide-preview-image');
                const video = $('#slide-preview-video');
                if (!url) {
                    video.trigger('pause').removeAttr('src poster');
                    image.removeAttr('src');
                    wrap.addClass('hidden');
                    return;
                }

                wrap.removeClass('hidden');
                $('#slide-preview-note').text(note || (mediaType === 'video' ? 'Xem trước video banner' : 'Xem trước ảnh banner'));
                if (mediaType === 'video') {
                    image.addClass('hidden').removeAttr('src');
                    video.removeClass('hidden').attr('src', url).attr('poster', poster || '');
                } else {
                    video.trigger('pause').addClass('hidden').removeAttr('src poster');
                    image.removeClass('hidden').attr('src', url);
                }
                if (laFileTam) slidePreviewUrl = url;
            };
            const dongDrawer = () => {
                datXemTruocSlide();
                window.closeDrawer('drawer-slide');
            };
            const inputClass = 'block w-full rounded-lg border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:bg-slate-700 dark:border-slate-600 dark:text-white';
            const themKhoiNoiDung = (block = {}) => {
                const row = $('<div class="grid grid-cols-[1fr_auto] gap-2 rounded-lg bg-slate-50 p-2 dark:bg-slate-700/50"><div class="space-y-2"><input type="text" maxlength="255" placeholder="Tiêu đề" class="hero-block-title"><textarea rows="2" maxlength="500" placeholder="Nội dung / mô tả" class="hero-block-content"></textarea></div><button type="button" title="Bớt nội dung" class="bot-khoi-noi-dung self-start rounded-lg px-2 py-1 text-sm font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">−</button></div>');
                row.find('input').addClass(inputClass).attr('name', 'content_blocks[][title]').val(block.title || '');
                row.find('textarea').addClass(inputClass).attr('name', 'content_blocks[][content]').val(block.content || '');
                $('#danh-sach-khoi-noi-dung').append(row);
            };
            const themCtaSlide = (cta = {}) => {
                const row = $('<div class="grid gap-2 rounded-lg bg-slate-50 p-2 dark:bg-slate-700/50 sm:grid-cols-[1fr_1.35fr_.8fr_auto_auto]"><input type="text" maxlength="60" placeholder="Tên nút" class="hero-cta-label"><input type="text" maxlength="255" placeholder="Link /shop hoặc https://..." class="hero-cta-link"><select class="hero-cta-style"><option value="primary">Nút chính</option><option value="secondary">Nút phụ</option><option value="ghost">Viền trong suốt</option></select><label class="flex items-center gap-1 text-[11px] text-slate-600 dark:text-slate-300"><input type="checkbox" class="hero-cta-new-tab">Tab mới</label><button type="button" title="Bớt CTA" class="bot-cta-slide rounded-lg px-2 py-1 text-sm font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">−</button></div>');
                row.find('.hero-cta-label').addClass(inputClass).attr('name', 'ctas[][label]').val(cta.label || '');
                row.find('.hero-cta-link').addClass(inputClass).attr('name', 'ctas[][link]').val(cta.link || '');
                row.find('.hero-cta-style').addClass(inputClass).attr('name', 'ctas[][style]').val(cta.style || 'primary');
                row.find('.hero-cta-new-tab').attr('name', 'ctas[][new_tab]').prop('checked', !!cta.new_tab);
                $('#danh-sach-cta-slide').append(row);
            };
            let heroPreviewMode = 'desktop';
            const capNhatPreviewHero = () => {
                const prefix = heroPreviewMode === 'desktop' ? 'desktop_layout' : 'mobile_layout';
                const layout = {
                    horizontal: $(`[name="${prefix}[horizontal]"]`).val() || 'center',
                    vertical: $(`[name="${prefix}[vertical]"]`).val() || 'center',
                    textAlign: $(`[name="${prefix}[text_align]"]`).val() || 'center',
                    overlay: Number($(`[name="${prefix}[overlay_opacity]"]`).val() || 35),
                };
                const horizontal = {left: 'justify-start', center: 'justify-center', right: 'justify-end'}[layout.horizontal];
                const vertical = {top: 'items-start', center: 'items-center', bottom: 'items-end'}[layout.vertical];
                const preview = $('#hero-layout-preview');
                preview.removeClass('aspect-[16/7] max-w-none mx-auto w-48 aspect-[4/5]').addClass(heroPreviewMode === 'desktop' ? 'aspect-[16/7]' : 'mx-auto w-48 aspect-[4/5]');
                preview.removeClass('justify-start justify-center justify-end items-start items-center items-end').addClass(`${horizontal} ${vertical}`);
                preview.find('[data-hero-overlay]').css('opacity', layout.overlay / 100);
                preview.find('[data-hero-preview-content]').css('text-align', layout.textAlign);
                $(`[data-overlay-value="${prefix}"]`).text(`${layout.overlay}%`);
            };
            const datNoiDungHero = (blocks, ctas, desktop = {}, mobile = {}) => {
                $('#danh-sach-khoi-noi-dung, #danh-sach-cta-slide').empty();
                (blocks && blocks.length ? blocks : [{}]).forEach(themKhoiNoiDung);
                (ctas && ctas.length ? ctas : [{}]).forEach(themCtaSlide);
                ['desktop_layout', 'mobile_layout'].forEach(prefix => {
                    const values = prefix === 'desktop_layout' ? desktop : mobile;
                    const fallback = prefix === 'desktop_layout'
                        ? {horizontal: 'left', vertical: 'center', text_align: 'left'}
                        : {horizontal: 'center', vertical: 'center', text_align: 'center'};
                    Object.entries(fallback).forEach(([key, value]) => {
                        $(`[name="${prefix}[${key}]"]`).val(values[key] || value);
                    });
                    $(`[name="${prefix}[focal_point]"]`).val(values.focal_point || 'center');
                    $(`[name="${prefix}[overlay_opacity]"]`).val(values.overlay_opacity ?? (prefix === 'desktop_layout' ? 35 : 45));
                });
                capNhatPreviewHero();
            };
            $('#them-khoi-noi-dung').on('click', () => themKhoiNoiDung());
            $('#them-cta-slide').on('click', () => themCtaSlide());
            $(document).on('click', '.bot-khoi-noi-dung, .bot-cta-slide', function() {
                const list = $(this).hasClass('bot-khoi-noi-dung') ? $('#danh-sach-khoi-noi-dung') : $('#danh-sach-cta-slide');
                if (list.children().length > 1) $(this).closest('div.grid').remove();
                else $(this).closest('div.grid').find('input, textarea').val('');
            });
            $(document).on('input change', '#form-slide [name^="desktop_layout"], #form-slide [name^="mobile_layout"]', capNhatPreviewHero);
            $(document).on('click', '.hero-preview-mode', function() {
                heroPreviewMode = $(this).data('hero-preview');
                $('.hero-preview-mode').removeClass('bg-white font-semibold text-slate-700 shadow-sm dark:bg-slate-600 dark:text-white').addClass('text-slate-500 dark:text-slate-300');
                $(this).addClass('bg-white font-semibold text-slate-700 shadow-sm dark:bg-slate-600 dark:text-white').removeClass('text-slate-500 dark:text-slate-300');
                capNhatPreviewHero();
            });
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
                datNoiDungHero();
                $('#vung-video, #canh-bao-anh').addClass('hidden');
                datXemTruocSlide();
                $('#slide-media').prop('required', true);
                moDrawer();
            });

            $(document).on('click', '.sua-slide', function() {
                const s = $(this).data('slide');
                const card = $(this).closest('.slide-card');
                idDangSua = s.id;
                $('#tieu-de-drawer').text('Sửa slide');
                $('#form-slide')[0].reset();

                const f = $('#form-slide');
                datNoiDungHero(
                    s.content_blocks || ((s.heading || s.subheading) ? [{title: s.heading || '', content: s.subheading || ''}] : []),
                    s.ctas || ((s.cta_label || s.cta_link) ? [{label: s.cta_label || '', link: s.cta_link || ''}] : []),
                    s.desktop_layout || {}, s.mobile_layout || {}
                );
                f.find('[name=alt]').val(s.alt || '');
                f.find('[name=starts_at]').val(s.starts_at ? s.starts_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=ends_at]').val(s.ends_at ? s.ends_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=status]').prop('checked', !!s.status);

                $('#slide-media').prop('required', false);
                $('#vung-video').toggleClass('hidden', s.media_type !== 'video');
                $('#canh-bao-anh').addClass('hidden');
                datXemTruocSlide(
                    card.attr('data-media-url'),
                    s.media_type,
                    s.media_type === 'video' ? 'Video hiện tại của slide' : 'Ảnh hiện tại của slide',
                    card.attr('data-poster-url')
                );
                moDrawer();
            });

            $('#slide-media').on('change', function() {
                const file = this.files[0];
                const canhBao = $('#canh-bao-anh').addClass('hidden').text('');
                if (!file) return;

                const laVideo = file.type.startsWith('video/');
                $('#vung-video').toggleClass('hidden', !laVideo);
                datXemTruocSlide(
                    URL.createObjectURL(file),
                    laVideo ? 'video' : 'image',
                    'Xem trước file mới: ' + file.name,
                    '',
                    true
                );

                const canhBaos = [];
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

            const dinhDangNgayGio = (date) => {
                const pad = (number) => String(number).padStart(2, '0');
                return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
            };

            $(document).on('click', '.lich-slide', function() {
                const preset = $(this).data('preset');
                const now = new Date();
                const form = $('#form-slide');
                if (preset === 'clear') {
                    form.find('[name=starts_at], [name=ends_at]').val('');
                    return;
                }
                form.find('[name=starts_at]').val(dinhDangNgayGio(now));
                form.find('[name=status]').prop('checked', true);
                if (preset === 'week') {
                    now.setDate(now.getDate() + 7);
                    form.find('[name=ends_at]').val(dinhDangNgayGio(now));
                }
            });

            $('#form-slide').submit(function(e) {
                e.preventDefault();
                $('#danh-sach-khoi-noi-dung > div').each(function(index) {
                    $(this).find('.hero-block-title').attr('name', `content_blocks[${index}][title]`);
                    $(this).find('.hero-block-content').attr('name', `content_blocks[${index}][content]`);
                });
                $('#danh-sach-cta-slide > div').each(function(index) {
                    $(this).find('.hero-cta-label').attr('name', `ctas[${index}][label]`);
                    $(this).find('.hero-cta-link').attr('name', `ctas[${index}][link]`);
                    $(this).find('.hero-cta-style').attr('name', `ctas[${index}][style]`);
                    $(this).find('.hero-cta-new-tab').attr('name', `ctas[${index}][new_tab]`);
                });
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

            $(document).on('click', '.bat-tat-slide', function() {
                const button = $(this);
                $.ajax({
                    url: '/content/banner/' + button.data('id') + '/toggle',
                    type: 'POST',
                    headers: csrf(),
                    success: function(r) {
                        window.showToast(r.success);
                        setTimeout(() => location.reload(), 350);
                    },
                    error: window.showAjaxError
                });
            });

            $(document).on('click', '.nhan-ban-slide', function() {
                const button = $(this);
                button.prop('disabled', true);
                $.ajax({
                    url: '/content/banner/' + button.data('id') + '/duplicate',
                    type: 'POST',
                    headers: csrf(),
                    success: function(r) {
                        window.showToast(r.success);
                        setTimeout(() => location.reload(), 500);
                    },
                    error: window.showAjaxError,
                    complete: function() { button.prop('disabled', false); }
                });
            });

            let slideDangKeo = null;
            let thuTuDaDoi = false;
            $(document).on('dragstart', '.slide-card', function(e) {
                slideDangKeo = this;
                thuTuDaDoi = false;
                $(this).addClass('opacity-50');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
            });
            $(document).on('dragover', '.slide-card', function(e) {
                e.preventDefault();
                if (!slideDangKeo || this === slideDangKeo) return;
                const rect = this.getBoundingClientRect();
                const datSau = e.originalEvent.clientY > rect.top + rect.height / 2;
                $(this)[datSau ? 'after' : 'before'](slideDangKeo);
                thuTuDaDoi = true;
            });
            $(document).on('dragend', '.slide-card', function() {
                $(this).removeClass('opacity-50');
                if (!slideDangKeo || !thuTuDaDoi) {
                    slideDangKeo = null;
                    return;
                }
                const ids = $('#danh-sach-slide .slide-card').map(function() { return $(this).data('id'); }).get();
                slideDangKeo = null;
                $.ajax({
                    url: '{{ route('content.banners.reorder') }}',
                    type: 'POST',
                    headers: csrf(),
                    data: { ids: ids },
                    success: function(r) { window.showToast(r.success); },
                    error: function(xhr) {
                        window.showAjaxError(xhr);
                        setTimeout(() => location.reload(), 700);
                    }
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
