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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Nhà cung cấp</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý nhà cung cấp
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btn-open-create-supplier"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm nhà cung cấp</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs mb-4 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-supplier"
                    class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                    placeholder="Tìm theo tên hoặc SĐT nhà cung cấp...">
                <button type="button" id="clearSupplierSearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
            </div>
        </div>
    </div>

    {{-- Supplier Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-4 w-4">
                            <input type="checkbox" id="checkall" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                        </th>
                        <th scope="col" class="p-4">Tên nhà cung cấp</th>
                        <th scope="col" class="p-4">Số điện thoại</th>
                        <th scope="col" class="p-4">Địa chỉ</th>
                        <th scope="col" class="p-4">Tổng tiền nhập</th>
                        <th scope="col" class="p-4">Trạng thái</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="supplierTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('supplier.data')
                </tbody>
            </table>
        </div>

        <div id="supplierPagination">
            {{ $supplier->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM NHÀ CUNG CẤP                                                 --}}
    {{-- ========================================================================= --}}
    <div id="drawer-create-supplier" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Thêm nhà cung cấp</h3>
            </div>
            <button type="button" class="close-supplier-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formAddSupplier" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tên nhà cung cấp <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="supplier_name" required placeholder="Ví dụ: Công ty Dệt May Việt Nhật"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Số điện thoại <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="supplier_phone" required placeholder="0901234567"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mã số thuế</label>
                    <input type="text" name="tax" placeholder="Ví dụ: 0101234567"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ</label>
                    <textarea name="address" rows="3" placeholder="Địa chỉ trụ sở / kho của nhà cung cấp..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-supplier-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Lưu nhà cung cấp</button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: SỬA NHÀ CUNG CẤP                                                  --}}
    {{-- ========================================================================= --}}
    <div id="drawer-update-supplier" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Cập nhật nhà cung cấp</h3>
            </div>
            <button type="button" class="close-supplier-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formEditSupplier" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tên nhà cung cấp <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="supplier_name" id="name_edit" required
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Số điện thoại <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="supplier_phone" id="supplier_phone_edit" required
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mã số thuế</label>
                    <input type="text" name="tax" id="tax_edit"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ</label>
                    <textarea id="address_edit" rows="3" name="address"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trạng thái hợp tác</label>
                    <select id="status_edit" name="status"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="1">Đang hợp tác</option>
                        <option value="0">Tạm dừng</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-supplier-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Cập nhật</button>
            </div>
        </form>
    </div>

    {{-- Delete Modal Confirmation --}}
    <x-modal-confirm id="modal-delete-supplier" title="Xóa nhà cung cấp" message="Bạn có chắc chắn muốn xóa nhà cung cấp này? Thao tác này không thể hoàn tác." />

    {{-- Scripts --}}
    <script>
        function reloadSuppliers() {
            $.get('{{ route('supplier.data') }}', function(data) {
                $('#supplierTable').html(data);
            });
        }

        $(document).ready(function() {
            let editSupplierId = null;
            let deleteSupplierId = null;

            const openDrawer = (id) => $('#' + id).removeClass('translate-x-full');
            const closeDrawer = (id) => $('#' + id).addClass('translate-x-full');

            $('.close-supplier-drawer').click(function() {
                closeDrawer('drawer-create-supplier');
                closeDrawer('drawer-update-supplier');
            });

            // Search
            $('#search-supplier').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearSupplierSearch').removeClass('hidden');
                else $('#clearSupplierSearch').addClass('hidden');

                $.get('{{ route('suppliers.search') }}', { keyword: val }, function(data) {
                    $('#supplierTable').html(data);
                });
            }, 300));

            $('#clearSupplierSearch').on('click', function() {
                $('#search-supplier').val('');
                $(this).addClass('hidden');
                reloadSuppliers();
            });

            // Checkall
            $('#checkall').change(function() {
                $('.checkitem').prop('checked', $(this).prop('checked'));
            });

            // Create Supplier
            $('#btn-open-create-supplier').click(function() {
                $('#formAddSupplier')[0].reset();
                openDrawer('drawer-create-supplier');
            });

            $('#formAddSupplier').submit(function(e) {
                e.preventDefault();
                window.submitFormWithProgress($(this), '{{ route('supplier.add') }}', function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-create-supplier');
                    reloadSuppliers();
                });
            });

            // Edit Supplier
            $(document).on('click', '.editSupplierButton', function() {
                editSupplierId = $(this).data('id-supplier');
                $.get('/supplier/getsupplier/' + editSupplierId, function(response) {
                    $('#name_edit').val(response.supplier_name);
                    $('#supplier_phone_edit').val(response.supplier_phone);
                    $('#address_edit').val(response.address);
                    $('#tax_edit').val(response.tax);
                    $('#status_edit').val(response.status == "1" ? "1" : "0");
                    openDrawer('drawer-update-supplier');
                }).fail(window.showAjaxError);
            });

            $('#formEditSupplier').submit(function(e) {
                e.preventDefault();
                if (!editSupplierId) return;
                window.submitFormWithProgress($(this), '/supplier/edit/' + editSupplierId, function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-update-supplier');
                    reloadSuppliers();
                });
            });

            // Delete Supplier Modal
            $(document).on('click', '.deleteSupplierButton', function() {
                deleteSupplierId = $(this).data('id-supplier');
                const name = $(this).data('name-supplier');
                $('#modal-delete-supplier-msg').text(`Bạn có chắc muốn xóa nhà cung cấp "${name}"?`);
                $('#modal-delete-supplier').removeClass('hidden');
            });

            $('#modal-delete-supplier-confirm').click(function() {
                if (!deleteSupplierId) return;
                $.ajax({
                    url: '/supplier/delete/' + deleteSupplierId,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        window.showToast(response.success);
                        $('#modal-delete-supplier').addClass('hidden');
                        reloadSuppliers();
                    },
                    error: window.showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
