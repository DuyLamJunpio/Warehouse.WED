<x-app-layout>
    <div class="p-4 bg-white border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Quản lý web bán hàng</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Slide ảnh, chữ quảng cáo, tiêu đề và bộ sưu tập trên web bán hàng. Lưu xong web tự cập nhật, không cần làm gì thêm.
        </p>
    </div>

    <div class="p-4 space-y-4">

        {{-- ══ 1. Slide hero ══════════════════════════════════════════ --}}
        <div class="bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Slide ảnh đầu trang</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ảnh lớn chạy luân phiên ở đầu trang chủ</p>
                </div>
                <button type="button" id="btn-them-slide"
                    class="px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                    + Thêm slide
                </button>
            </div>

            {{-- Hướng dẫn kích thước, để ngay chỗ tải file cho khỏi phải đi tìm --}}
            <div class="p-4 mx-4 mt-4 text-xs text-blue-900 rounded-lg bg-blue-50 dark:bg-gray-700 dark:text-blue-200">
                <p class="mb-1 font-semibold">Kích thước nên dùng</p>
                <ul class="space-y-1 list-disc list-inside">
                    <li><strong>Ảnh:</strong> tối thiểu {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px, nên xuất
                        2400×1600 (3:2) hoặc 2560×1440 (16:9). JPG/WebP/AVIF, dưới {{ $limits['anh_mb'] }}MB.</li>
                    <li><strong>Bố cục:</strong> đặt người mẫu ở khoảng giữa 70–80% khung hình theo cả chiều ngang lẫn
                        dọc. Điện thoại cắt dọc rất chặt (4:5), máy tính cắt ngang rất dẹt (16:7) — chủ thể lệch mép sẽ
                        bị cắt mất ở một trong hai.</li>
                    <li><strong>Video:</strong> 1920×1080 (16:9), MP4, dưới {{ $limits['video_mb'] }}MB, lặp ngắn 5–10
                        giây. Nên kèm ảnh bìa để không giật lúc tải, và ảnh riêng cho điện thoại để đỡ tốn dung lượng
                        mạng của khách.</li>
                </ul>
            </div>

            <div id="danh-sach-slide" class="p-4 space-y-3">
                @forelse ($banners as $b)
                    <div class="flex items-center gap-4 p-3 border rounded-lg dark:border-gray-700"
                        data-id="{{ $b->id }}">
                        <div class="w-32 overflow-hidden bg-gray-100 rounded shrink-0 aspect-video dark:bg-gray-700">
                            @if ($b->isVideo())
                                <video src="{{ Storage::url($b->media_path) }}" muted
                                    class="object-cover w-full h-full"></video>
                            @else
                                <img src="{{ Storage::url($b->media_path) }}" alt="{{ $b->alt }}"
                                    class="object-cover w-full h-full">
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate dark:text-white">
                                {{ $b->heading ?: '(không có tiêu đề)' }}
                            </div>
                            <div class="text-sm text-gray-500 truncate dark:text-gray-400">
                                {{ $b->subheading ?: '—' }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $b->isVideo() ? 'Video' : 'Ảnh' }}
                                @if ($b->starts_at || $b->ends_at)
                                    · Hiện từ {{ $b->starts_at?->format('d/m/Y H:i') ?: 'ngay' }}
                                    đến {{ $b->ends_at?->format('d/m/Y H:i') ?: 'khi tắt' }}
                                @endif
                            </div>
                        </div>

                        <span
                            class="px-2 py-1 text-xs font-medium rounded whitespace-nowrap {{ $b->is_live ? 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-300' : 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $b->is_live ? 'Đang hiện' : 'Đang ẩn' }}
                        </span>

                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" data-id="{{ $b->id }}" data-direction="up"
                                class="doi-thu-tu p-1.5 text-gray-500 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                title="Lên">▲</button>
                            <button type="button" data-id="{{ $b->id }}" data-direction="down"
                                class="doi-thu-tu p-1.5 text-gray-500 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                title="Xuống">▼</button>
                            <button type="button" data-slide="{{ $b->toJson() }}"
                                class="sua-slide px-3 py-1.5 text-sm text-white rounded bg-primary-700 hover:bg-primary-800">Sửa</button>
                            <button type="button" data-id="{{ $b->id }}"
                                data-name="{{ $b->heading ?: 'slide này' }}"
                                class="xoa-slide px-3 py-1.5 text-sm text-white bg-red-700 rounded hover:bg-red-800">Xoá</button>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                        Chưa có slide nào. Web đang dùng ảnh mặc định có sẵn trong mã nguồn.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ══ 2. Chữ chạy trên cùng ══════════════════════════════════ --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Chữ chạy nhỏ trên cùng</h2>
                <button type="button" id="btn-them-tb"
                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    + Thêm dòng
                </button>
            </div>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                Dải chữ nhỏ chạy sát mép trên. <strong>Hiện ở mọi trang</strong> chứ không riêng trang chủ — hợp để
                thông báo khuyến mãi, phí giao hàng, chính sách đổi trả.
            </p>

            <div id="danh-sach-tb" class="space-y-2">
                @foreach ($announcements as $a)
                    <div class="grid items-center grid-cols-12 gap-2 dong-tb">
                        <input type="text" value="{{ $a->value }}" maxlength="120"
                            placeholder="Ví dụ: Miễn phí giao hàng từ 500.000 ₫"
                            class="tb-value col-span-5 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <input type="datetime-local" value="{{ $a->starts_at?->format('Y-m-d\TH:i') }}"
                            class="tb-start col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <input type="datetime-local" value="{{ $a->ends_at?->format('Y-m-d\TH:i') }}"
                            class="tb-end col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <button type="button"
                            class="col-span-1 px-2 py-2 text-sm text-white bg-red-700 rounded-lg xoa-dong-tb hover:bg-red-800">×</button>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-12 gap-2 mt-1 text-xs text-gray-400 dark:text-gray-500">
                <span class="col-span-5">Nội dung</span>
                <span class="col-span-3">Bắt đầu hiện</span>
                <span class="col-span-3">Ngừng hiện</span>
            </div>

            <button type="button" id="btn-luu-tb"
                class="px-5 py-2.5 mt-4 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Lưu chữ trên cùng
            </button>
        </div>

        {{-- ══ 3. Chữ chạy lớn ════════════════════════════════════════ --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Chữ chạy lớn giữa trang chủ</h2>
                <button type="button" id="btn-them-chu"
                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    + Thêm dòng
                </button>
            </div>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                Dải chữ serif cỡ lớn nằm giữa trang chủ. Nên ngắn gọn 2–4 từ. Để trống ngày là hiện mãi.
            </p>

            <div id="danh-sach-chu" class="space-y-2">
                @foreach ($marquees as $m)
                    <div class="grid items-center grid-cols-12 gap-2 dong-chu">
                        <input type="text" value="{{ $m->value }}" maxlength="120"
                            placeholder="Ví dụ: Sale Tết đến 50%"
                            class="chu-value col-span-5 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <input type="datetime-local" value="{{ $m->starts_at?->format('Y-m-d\TH:i') }}"
                            class="chu-start col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <input type="datetime-local" value="{{ $m->ends_at?->format('Y-m-d\TH:i') }}"
                            class="chu-end col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <button type="button"
                            class="col-span-1 px-2 py-2 text-sm text-white bg-red-700 rounded-lg xoa-dong-chu hover:bg-red-800">×</button>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-12 gap-2 mt-1 text-xs text-gray-400 dark:text-gray-500">
                <span class="col-span-5">Nội dung</span>
                <span class="col-span-3">Bắt đầu hiện</span>
                <span class="col-span-3">Ngừng hiện</span>
            </div>

            <button type="button" id="btn-luu-chu"
                class="px-5 py-2.5 mt-4 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Lưu chữ chạy
            </button>
        </div>

        {{-- ══ 4. Bộ sưu tập ══════════════════════════════════════════ --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Bộ sưu tập</h2>
                <button type="button" id="btn-them-bst"
                    class="px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                    + Tạo bộ sưu tập
                </button>
            </div>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                Tự đặt tên và tích những sản phẩm muốn đưa vào — mùa đông tích đồ đông, hè tích đồ hè.
                Trang chủ hiện bộ sưu tập <strong>đang trong thời gian hiển thị</strong> đầu tiên.
            </p>

            <div class="space-y-3">
                @forelse ($collections as $bst)
                    <div class="flex items-center gap-4 p-3 border rounded-lg dark:border-gray-700">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $bst->title }}</div>
                            <div class="text-sm text-gray-500 truncate dark:text-gray-400">{{ $bst->subtitle ?: '—' }}</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $bst->products->count() }} sản phẩm
                                @if ($bst->starts_at || $bst->ends_at)
                                    · {{ $bst->starts_at?->format('d/m/Y') ?: 'ngay' }}
                                    → {{ $bst->ends_at?->format('d/m/Y') ?: 'khi tắt' }}
                                @endif
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded whitespace-nowrap {{ $bst->is_live ? 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-300' : 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $bst->is_live ? 'Đang hiện' : 'Đang ẩn' }}
                        </span>
                        <div class="flex gap-1 shrink-0">
                            <button type="button"
                                data-bst="{{ $bst->only(['id','title','subtitle','cta_label','cta_link','status']) ? json_encode(array_merge($bst->only(['id','title','subtitle','cta_label','cta_link','status']), ['starts_at'=>$bst->starts_at?->format('Y-m-d\TH:i'),'ends_at'=>$bst->ends_at?->format('Y-m-d\TH:i'),'product_ids'=>$bst->products->pluck('id')]), JSON_UNESCAPED_UNICODE) : '{}' }}"
                                class="sua-bst px-3 py-1.5 text-sm text-white rounded bg-primary-700 hover:bg-primary-800">Sửa</button>
                            <button type="button" data-id="{{ $bst->id }}" data-name="{{ $bst->title }}"
                                class="xoa-bst px-3 py-1.5 text-sm text-white bg-red-700 rounded hover:bg-red-800">Xoá</button>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                        Chưa có bộ sưu tập nào. Khối này trên web đang ẩn.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ══ 5. Tiêu đề các khối ════════════════════════════════════ --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tiêu đề các khối</h2>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                Để trống ô nào thì khối đó dùng lại chữ mặc định.
            </p>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @foreach ($headingLabels as $key => [$macDinh, $moTa])
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">{{ $moTa }}</label>
                        <input type="text" data-key="{{ $key }}" value="{{ $headings[$key] }}"
                            placeholder="{{ $macDinh }}" maxlength="255"
                            class="block w-full text-sm rounded-lg o-tieu-de bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Mặc định: {{ $macDinh }}</p>
                    </div>
                @endforeach
            </div>

            <button type="button" id="btn-luu-tieu-de"
                class="px-5 py-2.5 mt-4 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Lưu tiêu đề
            </button>
        </div>
    </div>

    {{-- ══ Ngăn thêm/sửa slide ════════════════════════════════════════ --}}
    <div id="drawer-slide"
        class="fixed top-0 right-0 z-40 w-full h-screen max-w-md p-4 overflow-y-auto transition-transform translate-x-full bg-white drawer dark:bg-gray-800"
        tabindex="-1" aria-hidden="true">
        <h5 id="tieu-de-drawer"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Thêm slide</h5>
        <button type="button" id="dong-drawer"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Đóng</span>
        </button>

        <form id="form-slide" class="space-y-4" enctype="multipart/form-data">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ảnh hoặc video *</label>
                <input type="file" name="media" id="slide-media"
                    accept="image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Ảnh ≥ {{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px, dưới {{ $limits['anh_mb'] }}MB ·
                    Video MP4 dưới {{ $limits['video_mb'] }}MB
                </p>
                <p id="canh-bao-anh" class="hidden mt-1 text-xs font-medium text-red-600"></p>
            </div>

            <div id="vung-video" class="hidden space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ảnh bìa video</label>
                    <input type="file" name="poster" accept="image/*"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Một khung hình tĩnh, để không bị giật lúc
                        video đang tải.</p>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ảnh cho điện
                        thoại</label>
                    <input type="file" name="mobile" accept="image/*"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Điện thoại sẽ hiện ảnh này thay vì tải
                        video, đỡ tốn dung lượng mạng của khách.</p>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Tiêu đề lớn</label>
                <input type="text" name="heading" maxlength="255" placeholder="Ví dụ: Bộ Sưu Tập Tết 2027"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Dòng mô tả</label>
                <textarea name="subheading" rows="2" maxlength="500"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Chữ trên nút</label>
                    <input type="text" name="cta_label" maxlength="60" placeholder="Mua ngay"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Nút dẫn tới</label>
                    <input type="text" name="cta_link" maxlength="255" placeholder="/shop"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Mô tả ảnh</label>
                <input type="text" name="alt" maxlength="255" placeholder="Người mẫu mặc áo khoác mùa mới"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dành cho người khiếm thị và công cụ tìm kiếm.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Bắt đầu hiện</label>
                    <input type="datetime-local" name="starts_at"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ngừng hiện</label>
                    <input type="datetime-local" name="ends_at"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Để trống cả hai thì slide hiện cho tới khi anh tắt.</p>

            <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                <input type="checkbox" name="status" value="1" checked
                    class="w-4 h-4 mr-2 border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                Bật slide này
            </label>

            <button type="submit"
                class="w-full px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Lưu slide
            </button>
        </form>
    </div>

    {{-- Ngăn tạo/sửa bộ sưu tập --}}
    <div id="drawer-bst"
        class="fixed top-0 right-0 z-40 w-full h-screen max-w-lg p-4 overflow-y-auto transition-transform translate-x-full bg-white drawer dark:bg-gray-800"
        tabindex="-1" aria-hidden="true">
        <h5 id="tieu-de-bst"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Tạo bộ sưu tập</h5>
        <button type="button" id="dong-drawer-bst"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Đóng</span>
        </button>

        <form id="form-bst" class="space-y-4">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Tên bộ sưu tập *</label>
                <input type="text" name="title" maxlength="255" required
                    placeholder="Ví dụ: Bộ sưu tập mùa đông / đi biển / Tết"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Dòng mô tả</label>
                <textarea name="subtitle" rows="2" maxlength="500"
                    class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Chữ trên nút</label>
                    <input type="text" name="cta_label" maxlength="60" placeholder="Mua ngay"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Nút dẫn tới</label>
                    <input type="text" name="cta_link" maxlength="255" placeholder="/shop"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Bắt đầu hiện</label>
                    <input type="datetime-local" name="starts_at"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Ngừng hiện</label>
                    <input type="datetime-local" name="ends_at"
                        class="block w-full text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Để trống cả hai thì bộ sưu tập hiện cho tới khi anh tắt. Đặt trước ngày để chuẩn bị bộ sưu tập Tết.
            </p>
            <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                <input type="checkbox" name="status" checked
                    class="w-4 h-4 mr-2 border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                Bật bộ sưu tập này
            </label>

            <div class="pt-3 border-t dark:border-gray-700">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Chọn sản phẩm (<span id="so-da-chon">0</span> đã chọn)
                </label>
                <input type="text" id="loc-sp" placeholder="Lọc theo tên sản phẩm hoặc danh mục"
                    class="block w-full mb-2 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <div class="p-2 overflow-y-auto border rounded-lg max-h-72 dark:border-gray-600">
                    @forelse ($allProducts as $sp)
                        <label
                            class="flex items-center gap-2 p-2 rounded cursor-pointer dong-sp hover:bg-gray-100 dark:hover:bg-gray-700"
                            data-ten="{{ mb_strtolower($sp->product_name . ' ' . ($sp->category->name ?? '')) }}">
                            <input type="checkbox"
                                class="w-4 h-4 border-gray-300 rounded chon-sp bg-gray-50 dark:bg-gray-700 dark:border-gray-600"
                                value="{{ $sp->id }}">
                            <span class="text-sm text-gray-900 dark:text-white">{{ $sp->product_name }}</span>
                            <span
                                class="ml-auto text-xs text-gray-500 dark:text-gray-400">{{ $sp->category->name ?? '-' }}</span>
                        </label>
                    @empty
                        <p class="p-3 text-sm text-gray-500 dark:text-gray-400">Chưa có sản phẩm nào để chọn.</p>
                    @endforelse
                </div>
            </div>

            <button type="submit"
                class="w-full px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800">
                Lưu bộ sưu tập
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let idDangSua = null;

            const baoLoi = (xhr) => {
                const res = xhr.responseJSON || {};
                if (res.errors) {
                    alert(Object.keys(res.errors).map(k => res.errors[k].join('\n')).join('\n'));
                } else {
                    alert(res.error || res.message || 'Lỗi: ' + xhr.statusText);
                }
            };

            const csrf = () => ({
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            });
            const moDrawer = () => $('#drawer-slide').removeClass('translate-x-full').attr('aria-hidden', 'false');
            const dongDrawer = () => $('#drawer-slide').addClass('translate-x-full').attr('aria-hidden', 'true');
            $('#dong-drawer').click(dongDrawer);

            // ── Slide ────────────────────────────────────────────────
            $('#btn-them-slide').click(function() {
                idDangSua = null;
                $('#tieu-de-drawer').text('Thêm slide');
                $('#form-slide')[0].reset();
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
                f.find('[name=cta_link]').val(s.cta_link || '');
                f.find('[name=alt]').val(s.alt || '');
                // Cắt bỏ giây và múi giờ: ô datetime-local chỉ nhận YYYY-MM-DDTHH:MM
                f.find('[name=starts_at]').val(s.starts_at ? s.starts_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=ends_at]').val(s.ends_at ? s.ends_at.slice(0, 16).replace(' ', 'T') : '');
                f.find('[name=status]').prop('checked', !!s.status);

                // Sửa thì không bắt buộc chọn lại file; bỏ trống là giữ nguyên file cũ.
                $('#slide-media').prop('required', false);
                $('#vung-video').toggleClass('hidden', s.media_type !== 'video');
                $('#canh-bao-anh').addClass('hidden');
                moDrawer();
            });

            // Kiểm tra ngay trên trình duyệt để khỏi tải file lên rồi mới báo lỗi.
            $('#slide-media').on('change', function() {
                const file = this.files[0];
                const canhBao = $('#canh-bao-anh').addClass('hidden').text('');
                if (!file) return;

                const laVideo = file.type.startsWith('video/');
                $('#vung-video').toggleClass('hidden', !laVideo);

                const mbToiDa = laVideo ? {{ $limits['video_mb'] }} : {{ $limits['anh_mb'] }};
                if (file.size > mbToiDa * 1024 * 1024) {
                    canhBao.removeClass('hidden').text(
                        `File nặng ${(file.size / 1048576).toFixed(1)}MB, vượt mức ${mbToiDa}MB.`);
                    return;
                }

                if (laVideo) return;

                const img = new Image();
                img.onload = function() {
                    if (img.width < {{ $limits['anh_rong'] }} || img.height < {{ $limits['anh_cao'] }}) {
                        canhBao.removeClass('hidden').text(
                            `Ảnh ${img.width}×${img.height}px, nhỏ hơn mức tối thiểu ` +
                            `{{ $limits['anh_rong'] }}×{{ $limits['anh_cao'] }}px — sẽ bị vỡ trên màn hình lớn.`);
                    }
                    URL.revokeObjectURL(img.src);
                };
                img.src = URL.createObjectURL(file);
            });

            $('#form-slide').submit(function(e) {
                e.preventDefault();
                const url = idDangSua ? '/content/banner/' + idDangSua : '/content/banner';

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: csrf(),
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(r) {
                        alert(r.success);
                        location.reload();
                    },
                    error: baoLoi
                });
            });

            $(document).on('click', '.xoa-slide', function() {
                if (!confirm('Xoá ' + $(this).data('name') + '?')) return;
                $.ajax({
                    url: '/content/banner/' + $(this).data('id'),
                    type: 'DELETE',
                    headers: csrf(),
                    success: function(r) {
                        alert(r.success);
                        location.reload();
                    },
                    error: baoLoi
                });
            });

            $(document).on('click', '.doi-thu-tu', function() {
                $.ajax({
                    url: '/content/banner/' + $(this).data('id') + '/reorder',
                    type: 'POST',
                    headers: csrf(),
                    data: {
                        direction: $(this).data('direction')
                    },
                    success: function() {
                        location.reload();
                    },
                    error: baoLoi
                });
            });

            // ── Chữ chạy ─────────────────────────────────────────────
            const dongChuMoi = () => $(`
                <div class="dong-chu grid grid-cols-12 gap-2 items-center">
                    <input type="text" maxlength="120" placeholder="Ví dụ: Sale Tết đến 50%"
                        class="chu-value col-span-5 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <input type="datetime-local"
                        class="chu-start col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <input type="datetime-local"
                        class="chu-end col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <button type="button" class="xoa-dong-chu col-span-1 px-2 py-2 text-sm text-white bg-red-700 rounded-lg hover:bg-red-800">×</button>
                </div>`);

            $('#btn-them-chu').click(() => $('#danh-sach-chu').append(dongChuMoi()));
            $(document).on('click', '.xoa-dong-chu', function() {
                $(this).closest('.dong-chu').remove();
            });

            $('#btn-luu-chu').click(function() {
                const items = [];
                let trong = false;

                $('#danh-sach-chu .dong-chu').each(function() {
                    const value = $(this).find('.chu-value').val().trim();
                    if (!value) {
                        trong = true;
                        return;
                    }
                    items.push({
                        value: value,
                        starts_at: $(this).find('.chu-start').val() || null,
                        ends_at: $(this).find('.chu-end').val() || null
                    });
                });

                if (trong && !confirm('Có dòng để trống, những dòng đó sẽ bị bỏ qua. Tiếp tục?')) return;

                $.ajax({
                    url: '{{ route('content.marquee') }}',
                    type: 'POST',
                    headers: csrf(),
                    data: {
                        items: items
                    },
                    success: function(r) {
                        alert(r.success);
                        location.reload();
                    },
                    error: baoLoi
                });
            });

            // ── Chữ chạy trên cùng ───────────────────────────────────
            const dongTbMoi = () => $(`
                <div class="dong-tb grid grid-cols-12 gap-2 items-center">
                    <input type="text" maxlength="120" placeholder="Ví dụ: Miễn phí giao hàng từ 500.000 ₫"
                        class="tb-value col-span-5 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <input type="datetime-local"
                        class="tb-start col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <input type="datetime-local"
                        class="tb-end col-span-3 text-sm rounded-lg bg-gray-50 border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <button type="button" class="xoa-dong-tb col-span-1 px-2 py-2 text-sm text-white bg-red-700 rounded-lg hover:bg-red-800">×</button>
                </div>`);

            $('#btn-them-tb').click(() => $('#danh-sach-tb').append(dongTbMoi()));
            $(document).on('click', '.xoa-dong-tb', function() {
                $(this).closest('.dong-tb').remove();
            });

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
                    url: '{{ route('content.marquee') }}',
                    type: 'POST',
                    headers: csrf(),
                    data: { items: items, group: 'announcement' },
                    success: function(r) { alert(r.success); location.reload(); },
                    error: baoLoi
                });
            });

            // ── Bộ sưu tập ───────────────────────────────────────────
            let idBst = null;

            const demDaChon = () => $('#so-da-chon').text($('.chon-sp:checked').length);
            const moBst = () => $('#drawer-bst').removeClass('translate-x-full').attr('aria-hidden', 'false');
            $('#dong-drawer-bst').click(() => $('#drawer-bst').addClass('translate-x-full').attr('aria-hidden', 'true'));
            $(document).on('change', '.chon-sp', demDaChon);

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
                const ids = $('.chon-sp:checked').map(function() {
                    return $(this).val();
                }).get();

                // Bộ sưu tập rỗng thì web ẩn khối đó, cảnh báo trước cho khỏi ngỡ ngàng.
                if (ids.length === 0 &&
                    !confirm('Chưa tích sản phẩm nào. Bộ sưu tập rỗng sẽ không hiện trên web. Vẫn lưu?')) return;

                $.ajax({
                    url: '/content/collection' + (idBst ? '/' + idBst : ''),
                    type: 'POST',
                    headers: csrf(),
                    data: {
                        title: $('#form-bst [name=title]').val(),
                        subtitle: $('#form-bst [name=subtitle]').val(),
                        cta_label: $('#form-bst [name=cta_label]').val(),
                        cta_link: $('#form-bst [name=cta_link]').val(),
                        starts_at: $('#form-bst [name=starts_at]').val() || null,
                        ends_at: $('#form-bst [name=ends_at]').val() || null,
                        status: $('#form-bst [name=status]').is(':checked') ? 1 : 0,
                        product_ids: ids
                    },
                    success: function(r) {
                        alert(r.success);
                        location.reload();
                    },
                    error: baoLoi
                });
            });

            $(document).on('click', '.xoa-bst', function() {
                if (!confirm('Xoá bộ sưu tập "' + $(this).data('name') + '"?')) return;
                $.ajax({
                    url: '/content/collection/' + $(this).data('id'),
                    type: 'DELETE',
                    headers: csrf(),
                    success: function(r) {
                        alert(r.success);
                        location.reload();
                    },
                    error: baoLoi
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
                    data: {
                        headings: headings
                    },
                    success: function(r) {
                        alert(r.success);
                    },
                    error: baoLoi
                });
            });
        });
    </script>
</x-app-layout>
