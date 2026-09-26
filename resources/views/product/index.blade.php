<x-app-layout>
    @php
        // Các khối cũ bên dưới chỉ còn để tương thích bookmark/script cũ.
        // Gợi ý thực tế cho form mới được khởi tạo theo ngành hàng trong JS.
        $variantQuickSizes = ['Nhỏ', 'Vừa', 'Lớn'];
        $variantQuickColors = ['Tiêu chuẩn', 'Cao cấp', 'Giới hạn'];
        $variantCombinationPresets = [
            [
                'label' => 'Một lựa chọn',
                'count' => '1 SKU',
                'value' => 'Tiêu chuẩn | Mặc định',
            ],
            [
                'label' => 'Ba lựa chọn',
                'count' => '3 SKU',
                'value' => 'Tiêu chuẩn/Cao cấp/Giới hạn | Mặc định',
            ],
            [
                'label' => 'Ba quy cách',
                'count' => '3 SKU',
                'value' => 'Mặc định | Nhỏ/Vừa/Lớn',
            ],
        ];
    @endphp
    {{-- Page Header & Breadcrumb --}}
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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Sản phẩm</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý sản phẩm
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" data-drawer-target="drawer-create-product-default" data-drawer-show="drawer-create-product-default"
                    id="createProductButton"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm sản phẩm</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-4 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            {{-- Search & Category Filter --}}
            <div class="flex flex-1 flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search-product"
                        class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                        placeholder="Tìm theo tên, mã sản phẩm...">
                    <button type="button" id="clearSearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="w-full sm:w-48">
                    <select id="filter-category"
                        class="block w-full py-2 px-3 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" data-filter="all" class="quick-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 transition-all">
                    Tất cả ({{ $products->total() }})
                </button>
                <button type="button" data-filter="in_stock" class="quick-filter-btn px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Còn hàng
                </button>
                <button type="button" data-filter="out_of_stock" class="quick-filter-btn px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Hết hàng
                </button>
                <button type="button" data-filter="featured" class="quick-filter-btn px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                    Nổi bật
                </button>
            </div>

        </div>
    </div>

    {{-- Products Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="w-4 p-4">
                            <input id="checkbox-all" type="checkbox"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                        </th>
                        <th scope="col" class="p-4">Sản phẩm</th>
                        <th scope="col" class="p-4">Giá bán / Vốn</th>
                        <th scope="col" class="p-4">Tồn kho</th>
                        <th scope="col" class="p-4">Nhà cung cấp</th>
                        <th scope="col" class="p-4">Trạng thái</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="productTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('product.data')
                </tbody>
            </table>
        </div>
        
        <div id="productPagination">
            {{ $products->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM SẢN PHẨM MỚI (CREATE PRODUCT)                               --}}
    {{-- ========================================================================= --}}
    <div id="drawer-create-product-default" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-2xl md:max-w-3xl h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Thêm sản phẩm mới</h3>
            </div>
            <button type="button" id="closeDrawerAdd" data-drawer-dismiss="drawer-create-product-default"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">
                ✕
            </button>
        </div>

        {{-- Form Content --}}
        <form id="formAdd" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-6 flex-1 overflow-y-auto custom-scrollbar">

                {{-- SECTION 1: THÔNG TIN CƠ BẢN --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 1. Thông tin cơ bản
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tên sản phẩm <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="product_name" required placeholder="VD: Nhang trầm hương không tăm — hộp 50 nén"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Danh mục <span class="text-rose-500">*</span>
                            </label>
                            <select name="categories_id" required
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                @foreach ($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nhà cung cấp
                            </label>
                            <select name="supplier_id"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                @foreach ($supplier as $item)
                                    <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành phần / nguyên liệu</label>
                            <input type="text" name="material" placeholder="VD: Bột trầm tự nhiên, keo bời lời..."
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phù hợp cho</label>
                            <select name="audience"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option value="Mọi không gian">Mọi không gian</option>
                                <option value="Thiền & thư giãn">Thiền & thư giãn</option>
                                <option value="Nghi lễ & thờ cúng">Nghi lễ & thờ cúng</option>
                                <option value="Quà tặng">Quà tặng</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả sản phẩm</label>
                            <textarea name="description" rows="2.5" placeholder="Mô tả nguồn nguyên liệu, mùi hương, thời gian sử dụng và cách bảo quản..."
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: HÌNH ẢNH & MEDIA --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 2. Hình ảnh & Video sản phẩm
                    </div>
                    
                    <label for="dropzone-file"
                        class="flex flex-col items-center justify-center w-full py-6 px-4 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl cursor-pointer bg-white dark:bg-slate-700/30 hover:bg-indigo-50/40 hover:border-indigo-300 transition-all">
                        <div class="flex flex-col items-center justify-center text-center">
                            <svg class="w-8 h-8 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Kéo thả hoặc bấm để chọn ảnh / video</p>
                            <p class="text-[11px] text-slate-400 mt-1">Hỗ trợ JPG, PNG, WEBP, MP4 (Click vào ảnh bất kỳ để chọn làm ảnh đại diện)</p>
                        </div>
                        <input id="dropzone-file" type="file" name="media[]" class="hidden" multiple accept="image/*,video/mp4" />
                    </label>

                    <div id="image-preview" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                    <input id="choose-image" type="hidden" name="pin_image" />
                </div>

                {{-- SECTION 3: GIÁ BÁN & QUY TẮC KHO --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3. Giá bán & Tồn kho
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá bán (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" inputmode="numeric" name="sell_price" required placeholder="0"
                                class="o-tien block w-full text-sm font-bold rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá vốn / Giá nhập (VNĐ)
                            </label>
                            <input type="text" inputmode="numeric" name="import_price" placeholder="Để trống nếu chưa có"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá khuyến mãi
                            </label>
                            <div class="flex gap-2">
                                <select name="discount_type" class="discount-type w-28 shrink-0 text-xs rounded-xl border-slate-300 bg-white px-2.5 py-2.5 focus:ring-1 focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                    <option value="amount">Giảm tiền</option>
                                    <option value="percent">Giảm %</option>
                                </select>
                                <input type="text" inputmode="numeric" name="discount_value" placeholder="Số tiền giảm"
                                    class="discount-value o-tien min-w-0 flex-1 text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            </div>
                            <p class="discount-preview mt-1 text-[11px] text-slate-500 dark:text-slate-400">Để trống nếu không giảm.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="manage_stock" value="1" checked
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200">Quản lý tồn kho theo biến thể</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200">Đánh dấu Sản phẩm Nổi bật</span>
                        </label>
                    </div>
                </div>

                {{-- SECTION 4: THUỘC TÍNH VÀ BIẾN THỂ --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Thuộc tính & biến thể
                            </div>
                            <p class="mt-1.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                Đặt tên thuộc tính theo sản phẩm của bạn. Mỗi dòng bên dưới là một SKU có giá và tồn kho riêng.
                            </p>
                        </div>
                        <button type="button" data-target="#styles-add"
                            class="addStyleCard shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            <span class="add-style-label">+ Thêm nhóm</span>
                        </button>
                    </div>
                    <div class="variant-setup rounded-xl border border-indigo-100 bg-white/80 p-3.5 dark:border-indigo-900/70 dark:bg-slate-800/50">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Chọn mẫu ngành hàng, rồi chỉnh tên nếu cần</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Sản phẩm một lựa chọn: dùng giá trị “Mặc định”.</p>
                        </div>
                        <div class="variant-preset-list mt-3 flex flex-wrap gap-1.5">
                            <button type="button" data-variant-preset="apparel" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Thời trang</button>
                            <button type="button" data-variant-preset="retail" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Bán lẻ</button>
                            <button type="button" data-variant-preset="food" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Thực phẩm & đồ uống</button>
                            <button type="button" data-variant-preset="beauty" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Mỹ phẩm</button>
                            <button type="button" data-variant-preset="custom" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Tùy chỉnh</button>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Nhóm chính
                                <input type="text" name="variant_attribute_labels[style]" data-variant-attribute="style" value="Phiên bản" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Lựa chọn thứ nhất
                                <input type="text" name="variant_attribute_labels[color]" data-variant-attribute="color" value="Lựa chọn 1" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Lựa chọn thứ hai
                                <input type="text" name="variant_attribute_labels[size]" data-variant-attribute="size" value="Lựa chọn 2" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                        </div>
                    </div>
                    <div id="styles-add" class="space-y-4"></div>
                </div>

                {{-- Khối ma trận cũ được giữ ẩn một phiên bản để các bookmark/form cũ không lỗi JS. --}}
                <div class="hidden">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Phân loại quy cách & mùi hương
                        </div>
                        <button type="button" data-target="#variants-add"
                            class="addVariantRow inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            + Thêm dòng
                        </button>
                    </div>

                    {{-- Quick Suggestion Chips --}}
                    <div class="space-y-3 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Quy cách:</span>
                            @foreach ($variantQuickSizes as $sz)
                                <button type="button" class="btn-chip-size px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-target="#variants-add" data-val="{{ $sz }}">
                                    + {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Mùi / phiên bản:</span>
                            @foreach ($variantQuickColors as $cl)
                                <button type="button" class="btn-chip-color px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-target="#variants-add" data-val="{{ $cl }}">
                                    + {{ $cl }}
                                </button>
                            @endforeach
                        </div>
                        <div class="rounded-xl border border-dashed border-indigo-200 bg-indigo-50/40 p-3 space-y-2 dark:border-indigo-900/70 dark:bg-indigo-950/20">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-slate-500 dark:text-slate-400 font-semibold mr-1">Gợi ý tổ hợp:</span>
                                @foreach ($variantCombinationPresets as $preset)
                                    <button type="button"
                                        class="btn-variant-preset inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white text-indigo-700 border border-indigo-100 hover:border-indigo-400 hover:bg-indigo-50 dark:bg-slate-800 dark:text-indigo-300 dark:border-indigo-900/70 dark:hover:bg-indigo-950/50"
                                        data-input="#variant-generator-add"
                                        data-val="{{ $preset['value'] }}"
                                        title="{{ $preset['value'] }}">
                                        {{ $preset['label'] }}
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $preset['count'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Bấm một mẫu để điền vào ô bên dưới, có thể sửa lại rồi nhấn Tạo nhanh hoặc Enter.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="variant-generator-add"
                            class="block flex-1 text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                            placeholder="VD: 10g,20g,50g | Trầm hương/Quế/Tuyết tùng">
                        <button type="button" class="btn-generate-variants inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all"
                            data-input="#variant-generator-add" data-target="#variants-add">
                            Tạo nhanh
                        </button>
                    </div>

                    <div class="space-y-2" id="variants-add">
                        {{-- Variant rows generated dynamically --}}
                    </div>
                </div>

            </div>

            {{-- Drawer Sticky Footer --}}
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" data-drawer-dismiss="drawer-create-product-default"
                    class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: CHỈNH SỬA SẢN PHẨM (EDIT PRODUCT)                                --}}
    {{-- ========================================================================= --}}
    <div id="drawer-update-product-default" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-2xl md:max-w-3xl h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Cập nhật sản phẩm</h3>
            </div>
            <button type="button" id="closeDrawerEdit" data-drawer-dismiss="drawer-update-product-default"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">
                ✕
            </button>
        </div>

        {{-- Form Content --}}
        <form id="formEdit" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-6 flex-1 overflow-y-auto custom-scrollbar">

                {{-- SECTION 1: THÔNG TIN CƠ BẢN --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 1. Thông tin cơ bản
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tên sản phẩm <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="product_name" id="product_name_edit" required
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Danh mục <span class="text-rose-500">*</span>
                            </label>
                            <select name="categories_id" id="categories_edit" required
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                @foreach ($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nhà cung cấp
                            </label>
                            <select name="supplier_id" id="supplier_edit"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                @foreach ($supplier as $item)
                                    <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành phần / nguyên liệu</label>
                            <input type="text" name="material" id="material_edit"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phù hợp cho</label>
                            <select name="audience" id="audience_edit"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option value="Mọi không gian">Mọi không gian</option>
                                <option value="Thiền & thư giãn">Thiền & thư giãn</option>
                                <option value="Nghi lễ & thờ cúng">Nghi lễ & thờ cúng</option>
                                <option value="Quà tặng">Quà tặng</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả sản phẩm</label>
                            <textarea name="description" id="description_edit" rows="2.5"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: HÌNH ẢNH & MEDIA --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 2. Hình ảnh & Video sản phẩm
                    </div>

                    {{-- Ảnh hiện tại --}}
                    <div>
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Ảnh hiện có:</div>
                        <div id="image-preview-edit" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                    </div>

                    <label for="dropzone-file-edit"
                        class="flex flex-col items-center justify-center w-full py-4 px-4 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl cursor-pointer bg-white dark:bg-slate-700/30 hover:bg-indigo-50/40 hover:border-indigo-300 transition-all">
                        <div class="flex flex-col items-center justify-center text-center">
                            <svg class="w-6 h-6 mb-1.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Tải thêm ảnh / video mới</p>
                        </div>
                        <input id="dropzone-file-edit" type="file" name="media[]" class="hidden" multiple accept="image/*,video/mp4" />
                    </label>

                    <div id="image-preview-edit-new" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                    <input id="choose-image-edit" type="hidden" name="pin_image" />
                </div>

                {{-- SECTION 3: GIÁ BÁN & TỒN KHO --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3. Giá bán & Tồn kho
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá bán (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" inputmode="numeric" name="sell_price" id="export_price_edit" required
                                class="o-tien block w-full text-sm font-bold rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá vốn / Giá nhập (VNĐ)
                            </label>
                            <input type="text" inputmode="numeric" name="import_price" id="import_price_edit" placeholder="Để trống nếu chưa có"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá khuyến mãi
                            </label>
                            <div class="flex gap-2">
                                <select name="discount_type" class="discount-type w-28 shrink-0 text-xs rounded-xl border-slate-300 bg-white px-2.5 py-2.5 focus:ring-1 focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                    <option value="amount">Giảm tiền</option>
                                    <option value="percent">Giảm %</option>
                                </select>
                                <input type="text" inputmode="numeric" name="discount_value" id="discount_value_edit" placeholder="Số tiền giảm"
                                    class="discount-value o-tien min-w-0 flex-1 text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            </div>
                            <p class="discount-preview mt-1 text-[11px] text-slate-500 dark:text-slate-400">Để trống nếu không giảm.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="manage_stock" id="manage_stock_edit" value="1"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200">Quản lý tồn kho theo biến thể</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" id="is_featured_edit" value="1"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200">Đánh dấu Sản phẩm Nổi bật</span>
                        </label>
                    </div>
                </div>

                {{-- SECTION 4: THUỘC TÍNH VÀ BIẾN THỂ --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Thuộc tính & biến thể
                            </div>
                            <p class="mt-1.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                Đổi tên ba thuộc tính để khớp ngành hàng. Mỗi dòng là một SKU có giá và tồn kho riêng.
                            </p>
                        </div>
                        <button type="button" data-target="#styles-edit"
                            class="addStyleCard shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            <span class="add-style-label">+ Thêm nhóm</span>
                        </button>
                    </div>
                    <div class="variant-setup rounded-xl border border-indigo-100 bg-white/80 p-3.5 dark:border-indigo-900/70 dark:bg-slate-800/50">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Chọn mẫu ngành hàng, rồi chỉnh tên nếu cần</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Sản phẩm một lựa chọn: dùng giá trị “Mặc định”.</p>
                        </div>
                        <div class="variant-preset-list mt-3 flex flex-wrap gap-1.5">
                            <button type="button" data-variant-preset="apparel" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Thời trang</button>
                            <button type="button" data-variant-preset="retail" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Bán lẻ</button>
                            <button type="button" data-variant-preset="food" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Thực phẩm & đồ uống</button>
                            <button type="button" data-variant-preset="beauty" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Mỹ phẩm</button>
                            <button type="button" data-variant-preset="custom" class="variant-preset rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Tùy chỉnh</button>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Nhóm chính
                                <input type="text" name="variant_attribute_labels[style]" data-variant-attribute="style" value="Phiên bản" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Lựa chọn thứ nhất
                                <input type="text" name="variant_attribute_labels[color]" data-variant-attribute="color" value="Lựa chọn 1" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Lựa chọn thứ hai
                                <input type="text" name="variant_attribute_labels[size]" data-variant-attribute="size" value="Lựa chọn 2" maxlength="40" required class="variant-attribute-label mt-1 block w-full rounded-lg border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </label>
                        </div>
                    </div>
                    <div id="styles-edit" class="space-y-4"></div>
                </div>

                {{-- Ma trận phẳng cũ không còn gửi dữ liệu; để ẩn trong giai đoạn chuyển tiếp. --}}
                <div class="hidden">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Phân loại quy cách & mùi hương
                        </div>
                        <button type="button" data-target="#variants-edit"
                            class="addVariantRow inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            + Thêm dòng
                        </button>
                    </div>

                    {{-- Quick Suggestion Chips --}}
                    <div class="space-y-3 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Quy cách:</span>
                            @foreach ($variantQuickSizes as $sz)
                                <button type="button" class="btn-chip-size px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-target="#variants-edit" data-val="{{ $sz }}">
                                    + {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Mùi / phiên bản:</span>
                            @foreach ($variantQuickColors as $cl)
                                <button type="button" class="btn-chip-color px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-target="#variants-edit" data-val="{{ $cl }}">
                                    + {{ $cl }}
                                </button>
                            @endforeach
                        </div>
                        <div class="rounded-xl border border-dashed border-indigo-200 bg-indigo-50/40 p-3 space-y-2 dark:border-indigo-900/70 dark:bg-indigo-950/20">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-slate-500 dark:text-slate-400 font-semibold mr-1">Gợi ý tổ hợp:</span>
                                @foreach ($variantCombinationPresets as $preset)
                                    <button type="button"
                                        class="btn-variant-preset inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white text-indigo-700 border border-indigo-100 hover:border-indigo-400 hover:bg-indigo-50 dark:bg-slate-800 dark:text-indigo-300 dark:border-indigo-900/70 dark:hover:bg-indigo-950/50"
                                        data-input="#variant-generator-edit"
                                        data-val="{{ $preset['value'] }}"
                                        title="{{ $preset['value'] }}">
                                        {{ $preset['label'] }}
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $preset['count'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Bấm một mẫu để điền vào ô bên dưới, có thể sửa lại rồi nhấn Tạo nhanh hoặc Enter.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="variant-generator-edit"
                            class="block flex-1 text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                            placeholder="VD: 10g,20g,50g | Trầm hương/Quế/Tuyết tùng">
                        <button type="button" class="btn-generate-variants inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all"
                            data-input="#variant-generator-edit" data-target="#variants-edit">
                            Tạo nhanh
                        </button>
                    </div>

                    <div class="space-y-2" id="variants-edit">
                        {{-- Edit variant rows --}}
                    </div>
                </div>

            </div>

            {{-- Drawer Sticky Footer --}}
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" data-drawer-dismiss="drawer-update-product-default"
                    class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>

    {{-- Modal Confirm Delete Product --}}
    <x-modal-confirm id="modal-delete-product" title="Xóa sản phẩm" message="Bạn có chắc chắn muốn xóa sản phẩm này không? Dữ liệu tồn kho và hình ảnh liên quan sẽ bị xóa." confirmText="Xóa vĩnh viễn" />

    {{-- Công thức combo: giữ dòng bán là 1 combo, tồn kho xuất theo thành phần. --}}
    <div id="modal-combo-components" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/50 p-4 sm:p-8">
        <div class="mx-auto my-4 w-full max-w-3xl rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
            <div class="flex items-start justify-between border-b border-slate-200 p-5 dark:border-slate-700">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Thiết lập thành phần combo</h3>
                    <p id="combo-product-name" class="mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                </div>
                <button type="button" class="close-combo-modal rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-700">✕</button>
            </div>
            <div class="p-5">
                <p class="mb-4 rounded-xl border border-violet-100 bg-violet-50 px-3 py-2 text-xs leading-5 text-violet-800 dark:border-violet-900/60 dark:bg-violet-950/30 dark:text-violet-200">
                    Mỗi dòng bán vẫn là <strong>1 combo</strong>. Ví dụ thêm “Thanh gỗ — Mặc định” với số lượng <strong>5</strong> thì khi xác nhận bán 1 combo, kho sẽ trừ 5 thanh gỗ.
                </p>
                <div id="combo-variants" class="space-y-4"></div>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 p-5 dark:border-slate-700">
                <button type="button" class="close-combo-modal rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Hủy</button>
                <button type="button" id="save-combo-components" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">Lưu công thức combo</button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            let editingProductId = null;
            let currentFilter = 'all';
            let variantRowIndex = 100;
            let styleRowIndex = 0;
            let currentProductImages = [];
            const defaultVariantLabels = {
                style: 'Phiên bản',
                color: 'Lựa chọn 1',
                size: 'Lựa chọn 2',
            };
            const industryVariantPresets = {
                apparel: {
                    labels: { style: 'Kiểu dáng', color: 'Màu sắc', size: 'Kích thước' },
                    colors: ['Đen', 'Trắng', 'Be', 'Xanh'],
                    sizes: ['S', 'M', 'L', 'XL'],
                    examples: [{ label: 'Áo cơ bản', value: 'Đen,Trắng | S,M,L' }],
                },
                retail: {
                    labels: { style: 'Phiên bản', color: 'Quy cách', size: 'Thông số / dung lượng' },
                    colors: ['Tiêu chuẩn', 'Cao cấp', 'Giới hạn'],
                    sizes: ['Nhỏ', 'Vừa', 'Lớn'],
                    examples: [{ label: 'Ba quy cách', value: 'Tiêu chuẩn | Nhỏ,Vừa,Lớn' }],
                },
                food: {
                    labels: { style: 'Hương vị', color: 'Quy cách', size: 'Khối lượng / đóng gói' },
                    colors: ['Hộp lẻ', 'Set dùng thử', 'Set quà'],
                    sizes: ['10g', '50g', '100g', '200g'],
                    examples: [{ label: 'Set & khối lượng', value: 'Hộp lẻ,Set quà | 50g,100g' }],
                },
                beauty: {
                    labels: { style: 'Dòng sản phẩm', color: 'Màu / mùi', size: 'Dung tích / kích thước' },
                    colors: ['Không màu', 'Hương dịu', 'Hương đậm'],
                    sizes: ['30ml', '50ml', '100ml'],
                    examples: [{ label: 'Màu & dung tích', value: 'Không màu,Hương dịu | 30ml,50ml' }],
                },
                custom: {
                    labels: defaultVariantLabels,
                    colors: ['Lựa chọn A', 'Lựa chọn B'],
                    sizes: ['Nhỏ', 'Lớn'],
                    examples: [{ label: 'Ví dụ 4 SKU', value: 'Lựa chọn A,Lựa chọn B | Nhỏ,Lớn' }],
                },
            };

            const normalizedLabel = (value, fallback) => String(value || '')
                .replace(/\s+/g, ' ')
                .trim()
                .slice(0, 40) || fallback;

            const variantLabelsFor = (form) => {
                const labels = {};
                Object.entries(defaultVariantLabels).forEach(([key, fallback]) => {
                    labels[key] = normalizedLabel(
                        form.find(`[data-variant-attribute="${key}"]`).val(),
                        fallback,
                    );
                });
                return labels;
            };

            const labelsForStyleCard = (card) => variantLabelsFor(card.closest('form'));

            const stylePresetFor = (form) => industryVariantPresets[form.data('variant-preset')]
                || industryVariantPresets.custom;

            const setStyleVariantRowLabels = (row, labels) => {
                row.find('.style-variant-color').attr({
                    placeholder: labels.color,
                    'aria-label': labels.color,
                });
                row.find('.style-variant-size').attr({
                    placeholder: labels.size,
                    'aria-label': labels.size,
                });
            };

            const renderStyleQuickChoices = (card) => {
                const form = card.closest('form');
                const labels = labelsForStyleCard(card);
                const preset = stylePresetFor(form);
                const colorChips = card.find('.style-color-chips').empty();
                const sizeChips = card.find('.style-size-chips').empty();
                const examples = card.find('.style-presets').empty();

                preset.colors.forEach(value => {
                    colorChips.append(
                        $('<button type="button">')
                            .addClass('style-color-chip px-2 py-0.5 rounded-md bg-slate-50 border border-slate-200 text-[10px] text-slate-600 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-300')
                            .attr('data-val', value)
                            .text(`+ ${labels.color}: ${value}`),
                    );
                });
                preset.sizes.forEach(value => {
                    sizeChips.append(
                        $('<button type="button">')
                            .addClass('style-size-chip px-2 py-0.5 rounded-md bg-slate-50 border border-slate-200 text-[10px] text-slate-600 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-300')
                            .attr('data-val', value)
                            .text(`+ ${labels.size}: ${value}`),
                    );
                });
                preset.examples.forEach(example => {
                    examples.append(
                        $('<button type="button">')
                            .addClass('style-preset px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-100 text-[10px] text-indigo-600 dark:bg-indigo-950/40 dark:border-indigo-900 dark:text-indigo-300')
                            .attr('data-val', example.value)
                            .text(example.label),
                    );
                });
            };

            const refreshStyleCardPresentation = (card) => {
                const labels = labelsForStyleCard(card);
                card.find('.style-name-label-text').text(`Giá trị ${labels.style}`);
                card.find('.style-name').attr('placeholder', `Ví dụ: ${labels.style} tiêu chuẩn`);
                card.find('.style-image-heading').text(`Ảnh của ${labels.style} (không bắt buộc)`);
                card.find('.style-image-help').text(`Chỉ chọn khi ${labels.style.toLowerCase()} này cần ảnh riêng; nếu để trống, web dùng ảnh sản phẩm.`);
                card.find('.style-variant-heading').text(`${labels.color}, ${labels.size}, giá và tồn kho`);
                card.find('.style-generator').attr('placeholder', `${labels.color} A,${labels.color} B | ${labels.size} 1,${labels.size} 2`);
                card.find('.style-variant-row').each(function() {
                    setStyleVariantRowLabels($(this), labels);
                });
                renderStyleQuickChoices(card);
            };

            const refreshVariantPresentation = (form) => {
                const labels = variantLabelsFor(form);
                form.find('.add-style-label').text(`+ Thêm ${labels.style}`);
                form.find('.style-card').each(function(index) {
                    const card = $(this);
                    card.find('.style-number').text(`${labels.style} ${index + 1}`);
                    refreshStyleCardPresentation(card);
                });
            };

            const applyVariantPreset = (form, presetKey, labels = null) => {
                const preset = industryVariantPresets[presetKey] || industryVariantPresets.custom;
                const nextLabels = labels || preset.labels;
                Object.entries(defaultVariantLabels).forEach(([key, fallback]) => {
                    form.find(`[data-variant-attribute="${key}"]`).val(
                        normalizedLabel(nextLabels[key], fallback),
                    );
                });
                form.data('variant-preset', presetKey);
                form.find('.variant-preset').removeClass('border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/60 dark:text-indigo-200');
                form.find(`.variant-preset[data-variant-preset="${presetKey}"]`)
                    .addClass('border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/60 dark:text-indigo-200');
                refreshVariantPresentation(form);
            };

            const renderDiscountPreview = (form) => {
                const mode = form.find('[name="discount_type"]').val();
                const rawValue = form.find('[name="discount_value"]').val();
                const sellPrice = parseInt(String(form.find('[name="sell_price"]').val() || '').replace(/\D/g, ''), 10) || 0;
                const preview = form.find('.discount-preview');

                if (!rawValue || !sellPrice) {
                    preview.text('Để trống nếu không giảm.');
                    return;
                }

                const reduction = mode === 'percent'
                    ? Math.round(sellPrice * (parseFloat(rawValue) || 0) / 100)
                    : (parseInt(String(rawValue).replace(/\D/g, ''), 10) || 0);
                const salePrice = Math.max(0, sellPrice - reduction);
                const actualPercent = Math.round((reduction / sellPrice) * 1000) / 10;
                const percentLabel = String(actualPercent).replace('.', ',');
                preview.text(`Giá khuyến mãi dự kiến: ${window.nhomNghin(salePrice)} ₫ · giảm ${percentLabel}%`);
            };

            const configureDiscountInput = (form, clearValue = false) => {
                const input = form.find('[name="discount_value"]');
                const isPercent = form.find('[name="discount_type"]').val() === 'percent';
                if (clearValue) input.val('');

                if (isPercent) {
                    input.removeClass('o-tien').attr({ type: 'number', inputmode: 'decimal', min: '0', max: '100', step: '0.1', placeholder: 'Ví dụ 10' });
                } else {
                    input.addClass('o-tien').attr({ type: 'text', inputmode: 'numeric', placeholder: 'Số tiền giảm' });
                    input.removeAttr('min max step');
                }

                renderDiscountPreview(form);
            };

            $('#formAdd, #formEdit').each(function() {
                const form = $(this);
                configureDiscountInput(form);
                form.on('change', '[name="discount_type"]', function() {
                    configureDiscountInput(form, true);
                });
                form.on('input change', '[name="discount_value"], [name="sell_price"]', function() {
                    renderDiscountPreview(form);
                });
            });

            const variantRow = (data) => {
                data = data || {};
                const i = variantRowIndex++;
                const qty = data.quantity !== undefined ? data.quantity : 0;
                const price = data.price_override ? window.nhomNghin(data.price_override) : '';
                return $(`
                    <div class="variant-row flex items-center gap-2 p-2.5 bg-white dark:bg-slate-700/60 rounded-xl border border-slate-200 dark:border-slate-600">
                        <input type="text" name="variants[${i}][size]" value="${data.size || ''}" maxlength="50" placeholder="Quy cách (10g, 50g...)"
                            class="w-24 text-xs font-semibold rounded-lg bg-slate-50 border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                        <input type="text" name="variants[${i}][color]" value="${data.color || ''}" maxlength="50" placeholder="Mùi / phiên bản"
                            class="w-28 text-xs rounded-lg bg-slate-50 border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                        <input type="number" min="0" name="variants[${i}][quantity]" value="${qty}" placeholder="SL tồn"
                            class="w-20 text-xs rounded-lg bg-slate-50 border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white text-center font-medium">
                        <input type="text" inputmode="numeric" name="variants[${i}][price_override]" value="${price}" placeholder="Giá riêng (nếu có)"
                            class="o-tien flex-1 text-xs rounded-lg bg-slate-50 border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                        <button type="button" class="removeVariantRow p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `);
            };

            /** Một SKU nằm trong đúng một giá trị của thuộc tính nhóm. */
            const styleVariantRow = (styleIndex, data, labels = defaultVariantLabels) => {
                data = data || {};
                const i = variantRowIndex++;
                const prefix = `styles[${styleIndex}][variants][${i}]`;
                const isPaused = !!data.paused || !!data.is_paused || (!!data.id && Number(data.quantity) === 0
                    && (!String(data.color || '').trim() || !String(data.size || '').trim()));
                const row = $(`
                    <div class="style-variant-row grid grid-cols-12 items-center gap-2 p-2.5 bg-slate-50/80 dark:bg-slate-800/70 rounded-xl border border-slate-200 dark:border-slate-600">
                        <input type="hidden" class="style-variant-id" name="${prefix}[id]">
                        <input type="hidden" class="style-variant-paused" name="${prefix}[paused]" value="0">
                        <input type="text" class="style-variant-color col-span-3 text-xs rounded-lg bg-white border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white" name="${prefix}[color]" maxlength="50" required>
                        <input type="text" class="style-variant-size col-span-2 text-xs font-semibold rounded-lg bg-white border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white" name="${prefix}[size]" maxlength="50" required>
                        <input type="number" class="style-variant-quantity col-span-2 text-xs rounded-lg bg-white border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white text-center font-medium" min="0" name="${prefix}[quantity]" placeholder="SL tồn">
                        <input type="text" class="style-variant-price o-tien col-span-4 text-xs rounded-lg bg-white border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white" inputmode="numeric" name="${prefix}[price_override]" placeholder="Giá riêng (nếu có)">
                        <button type="button" class="removeStyleVariant col-span-1 p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors" title="Bỏ biến thể">
                            <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `);

                row.find('.style-variant-id').val(data.id || '');
                row.find('.style-variant-color').val(data.color || '');
                row.find('.style-variant-size').val(data.size || '');
                row.find('.style-variant-quantity').val(data.quantity !== undefined ? data.quantity : 0);
                row.find('.style-variant-price').val(data.price_override ? window.nhomNghin(data.price_override) : '');
                row.find('.style-variant-paused').val(isPaused ? '1' : '0');
                setStyleVariantRowLabels(row, labels);
                row.attr('data-existing', data.id ? '1' : '0');
                row.attr('data-paused', isPaused ? '1' : '0');
                if (isPaused) {
                    row.find('.style-variant-color, .style-variant-size').prop('required', false);
                }
                if (data.id) {
                    const button = row.find('.removeStyleVariant');
                    button.data('default-icon', button.html());
                    if (isPaused) {
                        button.text('↺')
                            .attr('title', 'Khôi phục tồn kho trước khi tạm dừng')
                            .addClass('text-emerald-600 hover:text-emerald-700');
                    } else {
                        button.attr('title', 'Tạm dừng bán biến thể (đưa tồn kho về 0)');
                    }
                }
                return row;
            };

            const refreshStyleNumbers = (container) => {
                const target = $(container);
                refreshVariantPresentation(target.closest('form'));
            };

            const setStylePreview = (card, src) => {
                const frame = card.find('.style-image-preview');
                const image = frame.find('img');
                const oldObjectUrl = card.data('style-object-url');
                if (oldObjectUrl) {
                    URL.revokeObjectURL(oldObjectUrl);
                    card.removeData('style-object-url');
                }

                if (src) {
                    image.attr('src', src);
                    frame.removeClass('hidden');
                } else {
                    image.attr('src', '');
                    frame.addClass('hidden');
                }
            };

            /** Card nhóm: một giá trị thuộc tính + nhiều SKU con. */
            const styleCard = (data, labels = defaultVariantLabels) => {
                data = data || {};
                const styleIndex = styleRowIndex++;
                const prefix = `styles[${styleIndex}]`;
                const existing = !!data.id;
                const availableImages = [...currentProductImages];
                const card = $(`
                    <section class="style-card rounded-2xl border border-indigo-200/80 bg-white p-4 shadow-xs dark:border-indigo-900/70 dark:bg-slate-700/40" data-style-index="${styleIndex}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <span class="style-number text-[10px] font-bold uppercase tracking-wider text-indigo-500"></span>
                                <input type="hidden" class="style-id" name="${prefix}[id]">
                                <label class="mt-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"><span class="style-name-label-text">Giá trị nhóm</span> <span class="text-rose-500">*</span></label>
                                <input type="text" class="style-name mt-1 block w-full text-sm font-semibold rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-800 dark:border-slate-600 dark:text-white"
                                    name="${prefix}[name]" maxlength="100" required>
                            </div>
                            <button type="button" class="removeStyleCard ${existing ? 'hidden' : ''} mt-5 p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg" title="Bỏ nhóm chưa lưu">✕</button>
                        </div>

                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-[96px_1fr] gap-3 items-start">
                            <div class="style-image-preview hidden relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-600 dark:bg-slate-800">
                                <img alt="Ảnh mẫu" class="h-full w-full object-cover">
                            </div>
                            <div class="space-y-2">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600 hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <span class="style-image-heading">Ảnh riêng (không bắt buộc)</span>
                                    <input type="file" class="style-image-file hidden" name="${prefix}[image]" accept="image/jpeg,image/png,image/webp,image/gif">
                                </label>
                                <select class="style-image-select block w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-800 dark:text-white" name="${prefix}[image_id]">
                                    <option value="">Dùng ảnh sản phẩm đã có (không bắt buộc)</option>
                                </select>
                                <p class="style-image-help text-[10px] leading-4 text-slate-400">Chỉ dùng khi nhóm này cần ảnh riêng; nếu để trống, web dùng ảnh sản phẩm.</p>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-600">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="style-variant-heading text-[11px] font-bold uppercase tracking-wider text-slate-500">Lựa chọn, giá và tồn kho</p>
                                <button type="button" class="addStyleVariantRow px-2.5 py-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 rounded-lg dark:bg-indigo-950/60 dark:text-indigo-300">+ Thêm SKU</button>
                            </div>
                            <div class="style-color-chips mt-2 flex flex-wrap gap-1"></div>
                            <div class="style-size-chips mt-1.5 flex flex-wrap gap-1"></div>
                            <div class="mt-3 flex flex-col sm:flex-row gap-2">
                                <input type="text" class="style-generator block flex-1 text-xs rounded-xl border-slate-300 bg-white px-3 py-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                <button type="button" class="generateStyleVariants px-3 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl">Tạo tổ hợp</button>
                            </div>
                            <div class="style-presets mt-2 flex flex-wrap gap-1"></div>
                            <div class="style-variants mt-3 space-y-2"></div>
                        </div>
                    </section>
                `);

                card.find('.style-id').val(data.id || '');
                card.find('.style-name').val(data.name || '');

                const select = card.find('.style-image-select');
                availableImages
                    .filter(image => (image.media_type || 'image') === 'image')
                    .forEach(image => {
                        select.append($('<option>').val(image.id).text(image.name || `Ảnh #${image.id}`));
                    });
                select.val(data.image_model_id || '');

                const existingPath = data.image && data.image.path ? window.storageUrl(data.image.path) : '';
                setStylePreview(card, existingPath);
                const fileInput = card.find('.style-image-file');

                fileInput.on('change', function() {
                    const file = this.files && this.files[0];
                    if (!file) return;
                    const url = URL.createObjectURL(file);
                    setStylePreview(card, url);
                    card.data('style-object-url', url);
                    select.val('');
                });

                select.on('change', function() {
                    const image = availableImages.find(item => String(item.id) === String($(this).val()));
                    fileInput.val('');
                    setStylePreview(card, image ? window.storageUrl(image.path) : existingPath);
                });

                const variantsBox = card.find('.style-variants');
                (data.variants || []).forEach(variant => variantsBox.append(styleVariantRow(styleIndex, variant, labels)));
                if (!variantsBox.children().length) variantsBox.append(styleVariantRow(styleIndex, {
                    color: 'Mặc định',
                    size: 'Mặc định',
                    quantity: 0,
                }, labels));

                return card;
            };

            $(document).on('click', '.addVariantRow', function() {
                $($(this).data('target')).append(variantRow());
            });

            $(document).on('click', '.removeVariantRow', function() {
                $(this).closest('.variant-row').remove();
            });

            // Gợi ý chips nhanh
            $(document).on('click', '.btn-chip-size', function() {
                const target = $($(this).data('target'));
                const size = $(this).attr('data-val');
                if (!variantExists(target, size, '')) {
                    target.append(variantRow({ size: size, color: '', quantity: 0 }));
                }
            });

            $(document).on('click', '.btn-chip-color', function() {
                const target = $($(this).data('target'));
                const color = $(this).attr('data-val');
                if (!variantExists(target, '', color)) {
                    target.append(variantRow({ size: '', color: color, quantity: 0 }));
                }
            });

            const normalizeVariantValue = (value) => String(value || '').trim().toLowerCase();

            const variantExists = (container, size, color) => {
                const wantedSize = normalizeVariantValue(size);
                const wantedColor = normalizeVariantValue(color);
                let exists = false;

                container.find('.variant-row').each(function() {
                    const row = $(this);
                    const rowSize = normalizeVariantValue(row.find('input[name*="[size]"]').val());
                    const rowColor = normalizeVariantValue(row.find('input[name*="[color]"]').val());

                    if (rowSize === wantedSize && rowColor === wantedColor) {
                        exists = true;
                        return false;
                    }
                });

                return exists;
            };

            const parseVariantGenerator = (value) => {
                const parts = String(value || '').split('|');
                const split = (s) => String(s || '').split(/[,;/\n]+/)
                    .map(v => v.trim().slice(0, 50))
                    .filter(Boolean);

                const first = split(parts[0]);
                const second = split(parts.slice(1).join('|'));

                return {
                    // Trình tạo thuộc tính dùng thứ tự đang hiển thị: lựa chọn
                    // thứ nhất | lựa chọn thứ hai. Hai alias giữ khối legacy ẩn
                    // hoạt động cho tới khi có thể gỡ hẳn giao diện cũ.
                    first,
                    second,
                    sizes: first,
                    colors: second,
                };
            };

            const appendGeneratedVariants = (value, containerId) => {
                const parsed = parseVariantGenerator(value);
                if (!parsed.sizes.length && !parsed.colors.length) return 0;

                const container = $(containerId);
                let added = 0;

                (parsed.sizes.length ? parsed.sizes : ['']).forEach(size => {
                    (parsed.colors.length ? parsed.colors : ['']).forEach(color => {
                        if (variantExists(container, size, color)) return;

                        container.append(variantRow({ size: size, color: color, quantity: 0 }));
                        added++;
                    });
                });

                return added;
            };

            const runVariantGenerator = (inputId, containerId) => {
                const input = $(inputId);
                const value = input.val();
                const added = appendGeneratedVariants(value, containerId);

                if (added > 0) {
                    input.val('');
                }
            };

            $(document).on('click', '.btn-variant-preset', function() {
                const input = $($(this).data('input'));
                input.val($(this).attr('data-val')).focus();
            });

            $(document).on('click', '.btn-generate-variants', function() {
                runVariantGenerator($(this).data('input'), $(this).data('target'));
            });

            // Generator: S,M,L | Đen,Trắng hoặc S,M,L | Đen/Trắng
            const bindVariantGenerator = (inputId, containerId) => {
                $(inputId).on('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();

                    runVariantGenerator(inputId, containerId);
                });
            };

            bindVariantGenerator('#variant-generator-add', '#variants-add');
            bindVariantGenerator('#variant-generator-edit', '#variants-edit');

            const styleVariantExists = (card, size, color) => {
                const wantedSize = normalizeVariantValue(size);
                const wantedColor = normalizeVariantValue(color);
                let exists = false;

                card.find('.style-variant-row').each(function() {
                    const row = $(this);
                    const rowSize = normalizeVariantValue(row.find('.style-variant-size').val());
                    const rowColor = normalizeVariantValue(row.find('.style-variant-color').val());

                    if (rowSize === wantedSize && rowColor === wantedColor) {
                        exists = true;
                        return false;
                    }
                });

                return exists;
            };

            const appendStyleVariant = (card, data) => {
                const row = styleVariantRow(card.data('style-index'), data, labelsForStyleCard(card));
                card.find('.style-variants').append(row);
                return row;
            };

            const findCompatibleStyleVariant = (card, size, color) => {
                const wantedSize = normalizeVariantValue(size);
                const wantedColor = normalizeVariantValue(color);
                let compatible = $();

                card.find('.style-variant-row[data-existing="0"]').each(function() {
                    const row = $(this);
                    if (row.attr('data-paused') === '1') return;

                    const rowSize = normalizeVariantValue(row.find('.style-variant-size').val());
                    const rowColor = normalizeVariantValue(row.find('.style-variant-color').val());
                    const isDefaultRow = rowSize === 'mặc định' && rowColor === 'mặc định';
                    const sizeMatches = !rowSize || rowSize === wantedSize || isDefaultRow;
                    const colorMatches = !rowColor || rowColor === wantedColor || isDefaultRow;

                    if (sizeMatches && colorMatches) {
                        compatible = row;
                        return false;
                    }
                });

                return compatible;
            };

            const appendGeneratedStyleVariants = (card, value) => {
                const parsed = parseVariantGenerator(value);
                if (!parsed.first.length && !parsed.second.length) return 0;

                let added = 0;
                (parsed.first.length ? parsed.first : ['']).forEach(color => {
                    (parsed.second.length ? parsed.second : ['']).forEach(size => {
                        if (styleVariantExists(card, size, color)) return;

                        const compatible = findCompatibleStyleVariant(card, size, color);
                        if (compatible.length) {
                            compatible.find('.style-variant-size').val(size);
                            compatible.find('.style-variant-color').val(color);
                        } else {
                            appendStyleVariant(card, { size: size, color: color, quantity: 0 });
                        }
                        added++;
                    });
                });

                return added;
            };

            const fillStyleVariantField = (card, fieldSelector, value) => {
                let targetRow = $();
                card.find('.style-variant-row').each(function() {
                    const row = $(this);
                    if (row.attr('data-paused') === '1') return;
                    const currentValue = normalizeVariantValue(row.find(fieldSelector).val());
                    if (!currentValue || currentValue === 'mặc định') {
                        targetRow = row;
                        return false;
                    }
                });

                if (!targetRow.length) targetRow = appendStyleVariant(card);
                targetRow.find(fieldSelector).val(value).focus();
            };

            const disposeStyleCards = (container) => {
                $(container).find('.style-card').each(function() {
                    setStylePreview($(this), '');
                });
                $(container).empty();
            };

            const resetAddStyles = () => {
                currentProductImages = [];
                disposeStyleCards('#styles-add');
                const form = $('#formAdd');
                applyVariantPreset(form, 'custom');
                $('#styles-add').append(styleCard({
                    name: 'Mặc định',
                    variants: [{ color: 'Mặc định', size: 'Mặc định', quantity: 0 }],
                }, variantLabelsFor(form)));
                refreshStyleNumbers('#styles-add');
            };

            $(document).on('click', '.addStyleCard', function() {
                const targetSelector = $(this).data('target');
                if (targetSelector === '#styles-add') currentProductImages = [];

                const target = $(targetSelector);
                const card = styleCard({}, labelsForStyleCard(target.closest('form')));
                target.append(card);
                refreshStyleNumbers(target);
                card.find('.style-name').trigger('focus');
            });

            $(document).on('click', '.removeStyleCard', function() {
                const card = $(this).closest('.style-card');
                const container = card.parent();
                if (container.find('.style-card').length <= 1) {
                    window.showToast('Sản phẩm cần giữ lại ít nhất một nhóm thuộc tính.', 'warning');
                    return;
                }

                setStylePreview(card, '');
                card.remove();
                refreshStyleNumbers(container);
            });

            $(document).on('click', '.addStyleVariantRow', function() {
                const row = appendStyleVariant($(this).closest('.style-card'));
                row.find('.style-variant-color').trigger('focus');
            });

            $(document).on('click', '.removeStyleVariant', function() {
                const button = $(this);
                const row = button.closest('.style-variant-row');
                const container = row.closest('.style-variants');

                if (row.attr('data-existing') !== '1') {
                    if (container.find('.style-variant-row').length <= 1) {
                        window.showToast('Mỗi nhóm cần ít nhất một SKU có đủ hai lựa chọn.', 'warning');
                        return;
                    }
                    row.remove();
                    return;
                }

                const quantity = row.find('.style-variant-quantity');
                if (row.attr('data-paused') === '1') {
                    const previousQuantity = row.data('previous-quantity');
                    quantity.val(previousQuantity !== undefined ? previousQuantity : 0);
                    row.find('.style-variant-paused').val('0');
                    row.find('.style-variant-color, .style-variant-size').prop('required', true);
                    row.attr('data-paused', '0')
                        .removeClass('opacity-60 ring-1 ring-amber-300 dark:ring-amber-700');
                    button.html(button.data('default-icon'))
                        .attr('title', 'Tạm dừng bán biến thể (đưa tồn kho về 0)')
                        .removeClass('text-emerald-600 hover:text-emerald-700');
                    return;
                }

                button.data('default-icon', button.html());
                row.data('previous-quantity', quantity.val());
                quantity.val(0);
                row.find('.style-variant-paused').val('1');
                row.find('.style-variant-color, .style-variant-size').prop('required', false);
                row.attr('data-paused', '1')
                    .addClass('opacity-60 ring-1 ring-amber-300 dark:ring-amber-700');
                button.text('↺')
                    .attr('title', 'Khôi phục tồn kho trước khi tạm dừng')
                    .addClass('text-emerald-600 hover:text-emerald-700');
            });

            $(document).on('click', '.style-size-chip', function() {
                fillStyleVariantField($(this).closest('.style-card'), '.style-variant-size', $(this).attr('data-val'));
            });

            $(document).on('click', '.style-color-chip', function() {
                fillStyleVariantField($(this).closest('.style-card'), '.style-variant-color', $(this).attr('data-val'));
            });

            $(document).on('click', '.style-preset', function() {
                $(this).closest('.style-card').find('.style-generator')
                    .val($(this).attr('data-val'))
                    .trigger('focus');
            });

            $(document).on('click', '.generateStyleVariants', function() {
                const card = $(this).closest('.style-card');
                const input = card.find('.style-generator');
                if (appendGeneratedStyleVariants(card, input.val()) > 0) input.val('');
            });

            $(document).on('keydown', '.style-generator', function(e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                $(this).closest('.style-card').find('.generateStyleVariants').trigger('click');
            });

            $(document).on('click', '.variant-preset', function() {
                applyVariantPreset($(this).closest('form'), $(this).data('variant-preset'));
            });

            $(document).on('input', '.variant-attribute-label', function() {
                const form = $(this).closest('form');
                form.data('variant-preset', 'custom');
                form.find('.variant-preset').removeClass('border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/60 dark:text-indigo-200');
                form.find('.variant-preset[data-variant-preset="custom"]')
                    .addClass('border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/60 dark:text-indigo-200');
                refreshVariantPresentation(form);
            });

            resetAddStyles();

            // Media Pickers
            const addPicker = createMediaPicker('#dropzone-file', '#image-preview', '#choose-image');
            const editPicker = createMediaPicker('#dropzone-file-edit', '#image-preview-edit-new', '#choose-image-edit');

            const reloadDataTable = (url) => {
                const keyword = $('#search-product').val();
                const category = $('#filter-category').val();
                
                $.ajax({
                    url: url || '{{ route('product.data') }}',
                    type: 'GET',
                    data: { keyword: keyword, category_id: category, filter: currentFilter },
                    success: function(data) {
                        $('#productTable').html(data);
                        const newPagination = $('#productTable').find('#new-pagination-html').html();
                        if (newPagination !== undefined) {
                            $('#productPagination').html(newPagination);
                            $('#productTable').find('#ajax-pagination-data').remove();
                        }
                    },
                    error: window.showAjaxError
                });
            };

            // Search with Debounce
            $('#search-product').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearSearch').removeClass('hidden');
                else $('#clearSearch').addClass('hidden');
                reloadDataTable();
            }, 300));

            $('#clearSearch').on('click', function() {
                $('#search-product').val('');
                $(this).addClass('hidden');
                reloadDataTable();
            });

            $('#filter-category').on('change', function() {
                reloadDataTable();
            });

            $('.quick-filter-btn').on('click', function() {
                $('.quick-filter-btn').removeClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').addClass('text-slate-600 font-medium');
                $(this).addClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').removeClass('text-slate-600 font-medium');
                currentFilter = $(this).data('filter');
                reloadDataTable();
            });

            $(document).on('click', '#productPagination a', function(e) {
                e.preventDefault();
                const targetUrl = $(this).attr('href');
                if (targetUrl) reloadDataTable(targetUrl);
            });


            // Form Add Submit
            $('#formAdd').submit(function(e) {
                e.preventDefault();
                submitFormWithProgress($(this), '{{ route('product.add') }}', function(response) {
                    window.showToast(response.success);
                    $('#closeDrawerAdd').click();
                    $('#formAdd').trigger('reset');
                    configureDiscountInput($('#formAdd'));
                    $('#image-preview').empty();
                    addPicker.reset();
                    $('#variants-add').empty();
                    resetAddStyles();
                    reloadDataTable();
                });
            });

            // Form Edit Submit
            $('#formEdit').submit(function(e) {
                e.preventDefault();
                if (!editingProductId) return;

                submitFormWithProgress($(this), '/product/edit/' + editingProductId, function(response) {
                    window.showToast(response.success);
                    $('#closeDrawerEdit').click();
                    editPicker.reset();
                    disposeStyleCards('#styles-edit');
                    currentProductImages = [];
                    reloadDataTable();
                });
            });

            // Open Edit Drawer
            $(document).on('click', '.editProductButton', function() {
                const product_id = $(this).data('id-product');
                editingProductId = product_id;
                
                $('#image-preview-edit').empty();
                $('#image-preview-edit-new').empty();
                editPicker.reset();
                $('#formEdit').trigger('reset');
                configureDiscountInput($('#formEdit'));
                disposeStyleCards('#styles-edit');
                $('#variants-edit').empty();
                currentProductImages = [];

                $.ajax({
                    url: '/product/get-product/' + product_id,
                    type: 'GET',
                    success: function(response) {
                        const item = response[0];
                        $('#product_name_edit').val(item.product_name);
                        $('#import_price_edit').val(window.nhomNghin(item.import_price ?? ''));
                        $('#export_price_edit').val(window.nhomNghin(item.sell_price ?? ''));
                        $('#formEdit [name="discount_type"]').val('amount');
                        const discountReduction = item.discount_price !== null && item.discount_price !== undefined
                            && Number(item.discount_price) < Number(item.sell_price)
                            ? Number(item.sell_price) - Number(item.discount_price)
                            : '';
                        $('#discount_value_edit').val(discountReduction === '' ? '' : window.nhomNghin(discountReduction));
                        configureDiscountInput($('#formEdit'));
                        $('#material_edit').val(item.material);
                        $('#brand_edit').val(item.brand);
                        $('#audience_edit').val(item.audience || 'Mọi không gian');
                        $('#description_edit').val(item.description);
                        $('#is_featured_edit').prop('checked', !!item.is_featured);
                        $('#manage_stock_edit').prop('checked', !!item.manage_stock);
                        $('#categories_edit').val(item.categories_id);
                        $('#supplier_edit').val(item.supplier_id);

                        applyVariantPreset(
                            $('#formEdit'),
                            'custom',
                            item.variant_attribute_labels || defaultVariantLabels,
                        );

                        currentProductImages = Array.isArray(item.product_image) ? item.product_image : [];
                        const stylesBox = $('#styles-edit').empty();
                        const receivedStyles = Array.isArray(item.styles) ? item.styles : [];
                        const pinnedImage = currentProductImages.find(image => !!image.is_pined)
                            || currentProductImages.find(image => (image.media_type || 'image') === 'image')
                            || null;
                        const styles = receivedStyles.length ? receivedStyles.map(style => ({
                            ...style,
                            image: style.image || currentProductImages.find(image => String(image.id) === String(style.image_model_id)) || null,
                            variants: Array.isArray(style.variants)
                                ? style.variants
                                : (item.variants || []).filter(variant => String(variant.product_style_id) === String(style.id)),
                        })) : [{
                            name: 'Mẫu mặc định',
                            image_model_id: pinnedImage ? pinnedImage.id : null,
                            image: pinnedImage,
                            variants: item.variants || [],
                        }];

                        styles.forEach(style => stylesBox.append(styleCard(style, variantLabelsFor($('#formEdit')))));
                        refreshStyleNumbers(stylesBox);

                        const previewEdit = $('#image-preview-edit');
                        const pinInput = $('#choose-image-edit');

                        $.each(item.product_image || [], function(index, img) {
                            const isPined = !!img.is_pined;
                            if (isPined) pinInput.val(img.name);

                            const card = $(`
                                <div class="relative group rounded-xl overflow-hidden border ${isPined ? 'border-2 border-indigo-600 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700'} aspect-square bg-slate-100 dark:bg-slate-700 flex items-center justify-center cursor-pointer">
                                    <img src="${window.storageUrl(img.path)}" class="w-full h-full object-cover">
                                    <button type="button" class="btn-del-img absolute top-1 right-1 w-6 h-6 rounded-full bg-slate-900/70 text-white flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">✕</button>
                                </div>
                            `);

                            card.find('.btn-del-img').on('click', function(ev) {
                                ev.stopPropagation();
                                $.ajax({
                                    url: '/delete-image/' + img.id,
                                    type: 'DELETE',
                                    success: function() {
                                        $('#styles-edit .style-image-select').each(function() {
                                            if (String($(this).val()) === String(img.id)) {
                                                $(this).val('').trigger('change');
                                            }
                                            $(this).find(`option[value="${img.id}"]`).remove();
                                        });
                                        currentProductImages = currentProductImages.filter(image => String(image.id) !== String(img.id));
                                        card.remove();
                                        reloadDataTable();
                                    },
                                    error: window.showAjaxError
                                });
                            });

                            card.on('click', function() {
                                previewEdit.find('div').removeClass('border-2 border-indigo-600 ring-2 ring-indigo-500/20');
                                card.addClass('border-2 border-indigo-600 ring-2 ring-indigo-500/20');
                                pinInput.val(img.name);
                            });

                            previewEdit.append(card);
                        });

                        window.openDrawer('drawer-update-product-default');
                    },
                    error: window.showAjaxError
                });
            });

            // Delete Product with Modal Confirm
            let deleteTargetId = null;
            $(document).on('click', '.deleteProductButton', function() {
                deleteTargetId = $(this).data('id-product');
                const name = $(this).data('name-product');
                $('#modal-delete-product-message').html(`Bạn có chắc chắn muốn xóa sản phẩm <strong>${name}</strong> không? Toàn bộ dữ liệu liên quan sẽ bị xóa vĩnh viễn.`);
                $('#modal-delete-product').removeClass('hidden').addClass('flex');
            });

            $('#modal-delete-product-btn-confirm').on('click', function() {
                if (!deleteTargetId) return;
                $.ajax({
                    url: '/product/delete/' + deleteTargetId,
                    type: 'DELETE',
                    success: function(res) {
                        window.showToast(res.success);
                        $('#modal-delete-product').addClass('hidden').removeClass('flex');
                        reloadDataTable();
                    },
                    error: window.showAjaxError
                });
            });

            $('.btn-cancel-modal, [data-modal-hide]').on('click', function() {
                $('#modal-delete-product').addClass('hidden').removeClass('flex');
            });

            let comboState = { productId: null, componentOptions: [] };
            const escapeHtml = (value) => $('<div>').text(value ?? '').html();
            const comboComponentRow = (component = {}) => {
                const selectedId = String(component.component_variant_id || '');
                const options = comboState.componentOptions.map(option => `
                    <option value="${option.id}" ${String(option.id) === selectedId ? 'selected' : ''}>
                        ${escapeHtml(option.label)} · tồn ${option.stock}
                    </option>
                `).join('');

                return `
                    <div class="combo-component-row grid grid-cols-[1fr_90px_32px] items-center gap-2">
                        <select class="combo-component-id rounded-lg border-slate-200 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option value="">Chọn biến thể hàng vật lý</option>${options}
                        </select>
                        <input type="number" min="1" max="100000" value="${Number(component.quantity || 1)}" class="combo-component-quantity rounded-lg border-slate-200 px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white" aria-label="Số lượng thành phần">
                        <button type="button" class="remove-combo-component rounded-lg p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30" title="Bỏ thành phần">✕</button>
                    </div>`;
            };

            const renderComboVariants = (variants) => {
                $('#combo-variants').html(variants.map(variant => `
                    <section class="combo-variant-card rounded-xl border border-slate-200 p-4 dark:border-slate-700" data-combo-variant-id="${variant.id}">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">${escapeHtml(variant.label || 'Mặc định')}</h4>
                                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">SKU: ${escapeHtml(variant.sku)}</p>
                            </div>
                            <button type="button" class="add-combo-component rounded-lg border border-violet-200 px-2.5 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50 dark:border-violet-800 dark:text-violet-300">+ Thành phần</button>
                        </div>
                        <div class="combo-component-rows space-y-2">
                            ${(variant.components || []).map(component => comboComponentRow(component)).join('')}
                        </div>
                        <p class="combo-empty ${(variant.components || []).length ? 'hidden' : ''} mt-2 text-xs text-slate-400">Chưa có thành phần: biến thể này vẫn được tính là hàng bán lẻ.</p>
                    </section>
                `).join(''));
            };

            $(document).on('click', '.comboProductButton', function() {
                const productId = $(this).data('id-product');
                $('#combo-variants').html('<div class="py-10 text-center text-xs text-slate-400">Đang tải biến thể và công thức combo...</div>');
                $('#modal-combo-components').removeClass('hidden');

                $.ajax({
                    url: '/product/' + productId + '/combo-components',
                    type: 'GET',
                    success: function(data) {
                        comboState = { productId: data.product_id, componentOptions: data.component_options || [] };
                        $('#combo-product-name').text(data.product_name + ' · Chọn từng biến thể combo và định mức thành phần');
                        renderComboVariants(data.variants || []);
                    },
                    error: function(xhr) {
                        $('#modal-combo-components').addClass('hidden');
                        window.showAjaxError(xhr);
                    }
                });
            });

            $(document).on('click', '.close-combo-modal', function() {
                $('#modal-combo-components').addClass('hidden');
            });

            $(document).on('click', '.add-combo-component', function() {
                const card = $(this).closest('.combo-variant-card');
                card.find('.combo-component-rows').append(comboComponentRow());
                card.find('.combo-empty').addClass('hidden');
            });

            $(document).on('click', '.remove-combo-component', function() {
                const card = $(this).closest('.combo-variant-card');
                $(this).closest('.combo-component-row').remove();
                card.find('.combo-empty').toggleClass('hidden', card.find('.combo-component-row').length > 0);
            });

            $('#save-combo-components').on('click', function() {
                if (!comboState.productId) return;
                const components = [];
                let invalid = false;
                $('.combo-variant-card').each(function() {
                    const comboVariantId = $(this).data('combo-variant-id');
                    $(this).find('.combo-component-row').each(function() {
                        const componentVariantId = $(this).find('.combo-component-id').val();
                        const quantity = parseInt($(this).find('.combo-component-quantity').val() || '0', 10);
                        if (!componentVariantId || quantity < 1) {
                            invalid = true;
                            return;
                        }
                        components.push({
                            combo_variant_id: comboVariantId,
                            component_variant_id: componentVariantId,
                            quantity: quantity,
                        });
                    });
                });
                if (invalid) {
                    window.showToast('Hãy chọn thành phần và nhập số lượng từ 1 trở lên.', 'warning');
                    return;
                }

                const button = $(this).prop('disabled', true);
                $.ajax({
                    url: '/product/' + comboState.productId + '/combo-components',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { components: components },
                    success: function(response) {
                        window.showToast(response.success, 'success');
                        $('#modal-combo-components').addClass('hidden');
                        reloadDataTable();
                    },
                    error: window.showAjaxError,
                    complete: function() { button.prop('disabled', false); }
                });
            });
        });
    </script>

</x-app-layout>
