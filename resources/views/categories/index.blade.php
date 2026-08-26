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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Danh mục</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý danh mục sản phẩm
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btn-open-create-category"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm danh mục</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-4 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-categories"
                    class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                    placeholder="Tìm theo tên danh mục...">
                <button type="button" id="clearCategorySearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
            </div>
        </div>
    </div>

    {{-- Category Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-4">Ảnh</th>
                        <th scope="col" class="p-4">Tên danh mục</th>
                        <th scope="col" class="p-4">Đường dẫn (Slug)</th>
                        <th scope="col" class="p-4 text-center">Số lượng SP</th>
                        <th scope="col" class="p-4">Thứ tự</th>
                        <th scope="col" class="p-4">Trạng thái</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="categoriesTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('categories.data')
                </tbody>
            </table>
        </div>

        <div id="categoriesPagination">
            {{ $categories->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM DANH MỤC (CREATE CATEGORY)                                   --}}
    {{-- ========================================================================= --}}
    <div id="drawer-create-category" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Thêm danh mục mới</h3>
            </div>
            <button type="button" class="close-category-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formAddCategory" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tên danh mục <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Ví dụ: Áo sơ mi nam"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Danh mục cha</label>
                    <select id="parent_id" name="parent_id"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="">— Danh mục gốc (Không có cha) —</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả ngắn</label>
                    <textarea name="description" rows="3" placeholder="Mô tả danh mục sản phẩm..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh đại diện</label>
                    <input type="file" name="image" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trạng thái</label>
                    <select name="status"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="1">Đang sử dụng</option>
                        <option value="0">Ngưng sử dụng</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-category-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Lưu danh mục</button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: SỬA DANH MỤC (EDIT CATEGORY)                                     --}}
    {{-- ========================================================================= --}}
    <div id="drawer-update-category" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Cập nhật danh mục</h3>
            </div>
            <button type="button" class="close-category-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formEditCategory" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tên danh mục <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name_edit" required
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Danh mục cha</label>
                    <select id="parent_id_edit" name="parent_id"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="">— Danh mục gốc —</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mô tả</label>
                    <textarea id="description_edit" name="description" rows="3"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ảnh danh mục</label>
                    <input type="file" name="image" id="image_edit" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                    <p class="mt-1 text-[11px] text-slate-400">Bỏ trống nếu muốn giữ nguyên ảnh hiện tại.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trạng thái</label>
                    <select id="status_edit" name="status"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="1">Đang sử dụng</option>
                        <option value="0">Ngưng sử dụng</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-category-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Cập nhật</button>
            </div>
        </form>
    </div>

    {{-- Delete Modal Confirmation --}}
    <x-modal-confirm id="modal-delete-category" title="Xóa danh mục" message="Bạn có chắc chắn muốn xóa danh mục này? Thao tác này sẽ thất bại nếu còn sản phẩm hoặc danh mục con bên trong." />

    {{-- Scripts --}}
    <script>
        function reloadCategories() {
            $.get('{{ route('categories.data') }}', function(data) {
                $('#categoriesTable').html(data);
            });
        }

        $(document).ready(function() {
            let editCategoryId = null;
            let deleteCategoryId = null;

            const openDrawer = (id) => window.openDrawer(id);
            const closeDrawer = (id) => window.closeDrawer(id);


            // Search with debounce
            $('#search-categories').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearCategorySearch').removeClass('hidden');
                else $('#clearCategorySearch').addClass('hidden');

                $.get('{{ route('categories.search') }}', { keyword: val }, function(data) {
                    $('#categoriesTable').html(data);
                });
            }, 300));

            $('#clearCategorySearch').on('click', function() {
                $('#search-categories').val('');
                $(this).addClass('hidden');
                reloadCategories();
            });

            // Create Category
            $('#btn-open-create-category').click(function() {
                $('#formAddCategory')[0].reset();
                openDrawer('drawer-create-category');
            });

            $('#formAddCategory').submit(function(e) {
                e.preventDefault();
                window.submitFormWithProgress($(this), '{{ route('categories.add') }}', function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-create-category');
                    reloadCategories();
                });
            });

            // Edit Category
            $(document).on('click', '.editCategoriesButton', function() {
                editCategoryId = $(this).data('id-categories');
                $('#name_edit').val($(this).data('name-categories'));
                $('#description_edit').val($(this).data('description-categories') || '');
                $('#parent_id_edit').val($(this).data('parent-categories') || '');
                $('#status_edit').val($(this).data('status-categories') == 1 ? '1' : '0');

                $('#parent_id_edit option').prop('disabled', false);
                $('#parent_id_edit option[value="' + editCategoryId + '"]').prop('disabled', true);
                openDrawer('drawer-update-category');
            });

            $('#formEditCategory').submit(function(e) {
                e.preventDefault();
                if (!editCategoryId) return;
                window.submitFormWithProgress($(this), '/categories/edit/' + editCategoryId, function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-update-category');
                    reloadCategories();
                });
            });

            // Delete Category Modal
            $(document).on('click', '.deleteCategoriesButton', function() {
                deleteCategoryId = $(this).data('id-categories');
                const name = $(this).data('name-categories');
                $('#modal-delete-category-msg').text(`Bạn có chắc muốn xóa danh mục "${name}"?`);
                $('#modal-delete-category').removeClass('hidden');
            });

            $('#modal-delete-category-confirm').click(function() {
                if (!deleteCategoryId) return;
                $.ajax({
                    url: '/categories/delete/' + deleteCategoryId,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        window.showToast(response.success);
                        $('#modal-delete-category').addClass('hidden');
                        reloadCategories();
                    },
                    error: window.showAjaxError
                });
            });

            // Reorder
            $(document).on('click', '.reorderCategoriesButton', function() {
                $.ajax({
                    url: '/categories/reorder/' + $(this).data('id-categories'),
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { direction: $(this).data('direction') },
                    success: reloadCategories,
                    error: window.showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
