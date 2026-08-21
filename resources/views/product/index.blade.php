<x-app-layout>
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
                            <input type="text" name="product_name" required placeholder="VD: Áo Sơ Mi Lụa Cổ V Dài Tay"
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
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Chất liệu</label>
                            <input type="text" name="material" placeholder="VD: Lụa satin, Cotton, Kaki..."
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Đối tượng</label>
                            <select name="audience"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option value="Nữ">Nữ</option>
                                <option value="Unisex">Unisex</option>
                                <option value="Nam">Nam</option>
                                <option value="Trẻ em">Trẻ em</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả sản phẩm</label>
                            <textarea name="description" rows="2.5" placeholder="Mô tả dáng áo, form chuẩn, lưu ý giặt ủi..."
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
                            <input type="text" inputmode="numeric" name="import_price" placeholder="0"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá khuyến mãi (VNĐ)
                            </label>
                            <input type="text" inputmode="numeric" name="discount_price" placeholder="Bỏ trống nếu không giảm"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
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

                {{-- SECTION 4: MA TRẬN BIẾN THỂ (SIZE / MÀU) --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Biến thể Size & Màu (Tồn kho)
                        </div>
                        <button type="button" data-target="#variants-add"
                            class="addVariantRow inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            + Thêm dòng
                        </button>
                    </div>

                    {{-- Quick Suggestion Chips --}}
                    <div class="space-y-2 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Size gợi ý:</span>
                            @foreach (['S', 'M', 'L', 'XL', '2XL', 'Freesize'] as $sz)
                                <button type="button" class="btn-chip-size px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-val="{{ $sz }}">
                                    + {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 font-medium mr-1">Màu gợi ý:</span>
                            @foreach (['Đen', 'Trắng', 'Be', 'Xanh Navy', 'Hồng', 'Nâu', 'Xám', 'Đỏ'] as $cl)
                                <button type="button" class="btn-chip-color px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:border-indigo-400 text-slate-700 dark:text-slate-300" data-val="{{ $cl }}">
                                    + {{ $cl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative">
                        <input type="text" id="variant-generator-add"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                            placeholder="Gõ nhanh tổ hợp: S,M,L | Đen,Trắng  →  nhấn Enter">
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
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Chất liệu</label>
                            <input type="text" name="material" id="material_edit"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Đối tượng</label>
                            <select name="audience" id="audience_edit"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option value="Nữ">Nữ</option>
                                <option value="Unisex">Unisex</option>
                                <option value="Nam">Nam</option>
                                <option value="Trẻ em">Trẻ em</option>
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
                            <input type="text" inputmode="numeric" name="import_price" id="import_price_edit"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Giá khuyến mãi (VNĐ)
                            </label>
                            <input type="text" inputmode="numeric" name="discount_price" id="discount_price_edit"
                                class="o-tien block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
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

                {{-- SECTION 4: MA TRẬN BIẾN THỂ --}}
                <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 4. Biến thể Size & Màu (Tồn kho)
                        </div>
                        <button type="button" data-target="#variants-edit"
                            class="addVariantRow inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-all">
                            + Thêm dòng
                        </button>
                    </div>

                    <div class="relative">
                        <input type="text" id="variant-generator-edit"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                            placeholder="Gõ nhanh tổ hợp: S,M,L | Đen,Trắng  →  nhấn Enter">
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

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            let editingProductId = null;
            let currentFilter = 'all';
            let variantRowIndex = 100;

            const variantRow = (data) => {
                data = data || {};
                const i = variantRowIndex++;
                const qty = data.quantity !== undefined ? data.quantity : 0;
                const price = data.price_override ? window.nhomNghin(data.price_override) : '';
                return $(`
                    <div class="variant-row flex items-center gap-2 p-2.5 bg-white dark:bg-slate-700/60 rounded-xl border border-slate-200 dark:border-slate-600">
                        <input type="text" name="variants[${i}][size]" value="${data.size || ''}" maxlength="50" placeholder="Size (S, M...)"
                            class="w-24 text-xs font-semibold rounded-lg bg-slate-50 border-slate-300 p-2 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                        <input type="text" name="variants[${i}][color]" value="${data.color || ''}" maxlength="50" placeholder="Màu (Đen, Be...)"
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

            $(document).on('click', '.addVariantRow', function() {
                $($(this).data('target')).append(variantRow());
            });

            $(document).on('click', '.removeVariantRow', function() {
                $(this).closest('.variant-row').remove();
            });

            // Gợi ý chips nhanh
            $(document).on('click', '.btn-chip-size', function() {
                const target = $(this).closest('.space-y-4').find('#variants-add');
                target.append(variantRow({ size: $(this).data('val'), color: '', quantity: 0 }));
            });
            $(document).on('click', '.btn-chip-color', function() {
                const target = $(this).closest('.space-y-4').find('#variants-add');
                target.append(variantRow({ size: '', color: $(this).data('val'), quantity: 0 }));
            });

            // Generator: S,M,L | Đen,Trắng
            const bindVariantGenerator = (inputId, containerId) => {
                $(inputId).on('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();

                    const parts = $(this).val().split('|');
                    const split = (s) => (s || '').split(/[,;/\n]+/).map(v => v.trim().slice(0, 50)).filter(Boolean);
                    const sizes = split(parts[0]);
                    const colors = split(parts[1]);

                    if (!sizes.length && !colors.length) return;

                    const container = $(containerId);
                    (sizes.length ? sizes : ['']).forEach(s => {
                        (colors.length ? colors : ['']).forEach(c => {
                            container.append(variantRow({ size: s, color: c, quantity: 0 }));
                        });
                    });

                    $(this).val('');
                });
            };

            bindVariantGenerator('#variant-generator-add', '#variants-add');
            bindVariantGenerator('#variant-generator-edit', '#variants-edit');

            // Media Pickers
            const addPicker = createMediaPicker('#dropzone-file', '#image-preview', '#choose-image');
            const editPicker = createMediaPicker('#dropzone-file-edit', '#image-preview-edit-new', '#choose-image-edit');

            const reloadDataTable = () => {
                const keyword = $('#search-product').val();
                const category = $('#filter-category').val();
                
                $.ajax({
                    url: '{{ route('product.data') }}',
                    type: 'GET',
                    data: { keyword: keyword, category_id: category, filter: currentFilter },
                    success: function(data) {
                        $('#productTable').html(data);
                    }
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

            $('#filter-category').on('change', reloadDataTable);

            $('.quick-filter-btn').on('click', function() {
                $('.quick-filter-btn').removeClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').addClass('text-slate-600 font-medium');
                $(this).addClass('bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 font-semibold').removeClass('text-slate-600 font-medium');
                currentFilter = $(this).data('filter');
                reloadDataTable();
            });

            // Form Add Submit
            $('#formAdd').submit(function(e) {
                e.preventDefault();
                submitFormWithProgress($(this), '{{ route('product.add') }}', function(response) {
                    window.showToast(response.success);
                    $('#closeDrawerAdd').click();
                    $('#formAdd').trigger('reset');
                    $('#image-preview').empty();
                    addPicker.reset();
                    $('#variants-add').empty();
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

                $.ajax({
                    url: '/product/get-product/' + product_id,
                    type: 'GET',
                    success: function(response) {
                        const item = response[0];
                        $('#product_name_edit').val(item.product_name);
                        $('#import_price_edit').val(window.nhomNghin(item.import_price ?? ''));
                        $('#export_price_edit').val(window.nhomNghin(item.sell_price ?? ''));
                        $('#discount_price_edit').val(window.nhomNghin(item.discount_price ?? ''));
                        $('#material_edit').val(item.material);
                        $('#brand_edit').val(item.brand);
                        $('#audience_edit').val(item.audience || 'Nữ');
                        $('#description_edit').val(item.description);
                        $('#is_featured_edit').prop('checked', !!item.is_featured);
                        $('#manage_stock_edit').prop('checked', !!item.manage_stock);
                        $('#categories_edit').val(item.categories_id);
                        $('#supplier_edit').val(item.supplier_id);

                        const variantsBox = $('#variants-edit').empty();
                        $.each(item.variants || [], function(i, v) {
                            variantsBox.append(variantRow(v));
                        });

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
        });
    </script>

</x-app-layout>
