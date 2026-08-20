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
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Khách hàng</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Quản lý khách hàng
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" id="btn-open-create-customer"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Thêm khách hàng</span>
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
                <input type="text" id="search-customer"
                    class="block w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700/50 dark:border-slate-600 dark:text-white placeholder-slate-400"
                    placeholder="Tìm theo tên, số điện thoại, địa chỉ...">
                <button type="button" id="clearCustomerSearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">✕</button>
            </div>
        </div>
    </div>

    {{-- Customer Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-800/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-4">Khách hàng</th>
                        <th scope="col" class="p-4">Số điện thoại</th>
                        <th scope="col" class="p-4">Địa chỉ giao hàng</th>
                        <th scope="col" class="p-4 text-center">Số đơn</th>
                        <th scope="col" class="p-4">Tổng chi tiêu</th>
                        <th scope="col" class="p-4">Hạng khách</th>
                        <th scope="col" class="p-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="customerTable" class="divide-y divide-slate-200/80 dark:divide-slate-700/80">
                    @include('customer.data')
                </tbody>
            </table>
        </div>

        <div id="customerPagination">
            {{ $customers->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: THÊM KHÁCH HÀNG (CREATE CUSTOMER)                                 --}}
    {{-- ========================================================================= --}}
    <div id="drawer-create-customer" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Thêm khách hàng mới</h3>
            </div>
            <button type="button" class="close-customer-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formAddCustomer" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Họ và tên <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="customer_name" required placeholder="Nguyễn Văn A"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Số điện thoại <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" required placeholder="0901234567"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input type="email" name="customer_email" placeholder="email@example.com"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tỉnh / Thành phố</label>
                        <input type="text" name="province" placeholder="TP. Hồ Chí Minh"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phường / Xã</label>
                        <input type="text" name="ward" placeholder="Phường Bến Nghé"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ chi tiết</label>
                    <textarea name="address" rows="2" placeholder="Số nhà, tên đường..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú chăm sóc</label>
                    <textarea name="note" rows="2" placeholder="Khách quen, sở thích, yêu cầu đặc biệt..."
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-customer-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Lưu khách hàng</button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: SỬA KHÁCH HÀNG (EDIT CUSTOMER)                                   --}}
    {{-- ========================================================================= --}}
    <div id="drawer-update-customer" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-md h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Cập nhật khách hàng</h3>
            </div>
            <button type="button" class="close-customer-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <form id="formEditCustomer" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
            @csrf
            <div class="p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Họ và tên <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="customer_name" id="customer_name_edit" required
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Số điện thoại <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" id="customer_phone_edit" required
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input type="email" name="customer_email" id="customer_email_edit"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Trạng thái / Nhóm</label>
                    <select id="status_edit" name="status"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <option value="0">Khách thường</option>
                        <option value="1">Khách VIP</option>
                        <option value="2">Đã khóa</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tỉnh / Thành phố</label>
                        <input type="text" name="province" id="province_edit"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phường / Xã</label>
                        <input type="text" name="ward" id="ward_edit"
                            class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ chi tiết</label>
                    <textarea id="address_edit" name="address" rows="2"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú chăm sóc</label>
                    <textarea id="note_edit" name="note" rows="2"
                        class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
                <button type="button" class="close-customer-drawer px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Hủy</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">Cập nhật</button>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: HỒ SƠ KHÁCH HÀNG & LỊCH SỬ (CUSTOMER PROFILE)                     --}}
    {{-- ========================================================================= --}}
    <div id="drawer-customer-profile" tabindex="-1" aria-hidden="true"
        class="fixed top-0 right-0 z-40 w-full sm:max-w-lg h-screen overflow-y-auto transition-transform translate-x-full bg-white dark:bg-slate-800 shadow-2xl flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm z-10">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Hồ sơ khách hàng</h3>
            </div>
            <button type="button" class="close-customer-drawer p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 rounded-lg">✕</button>
        </div>

        <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar" id="profile-body">
            {{-- Loaded via AJAX --}}
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 sticky bottom-0">
            <label for="profile-note" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                Ghi chú chăm sóc khách hàng
            </label>
            <textarea id="profile-note" rows="2"
                class="block w-full text-xs rounded-xl bg-white border-slate-300 p-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                placeholder="Ghi chú sở thích, size quần áo, lịch sử tư vấn..."></textarea>
            <button type="button" id="btn-save-note"
                class="w-full mt-2.5 px-4 py-2 text-xs font-semibold text-white rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition-all">
                Lưu ghi chú CRM
            </button>
        </div>
    </div>

    {{-- Confirmation Modal for Delete --}}
    <x-modal-confirm id="modal-delete-customer" title="Xóa khách hàng" message="Bạn có chắc chắn muốn xóa khách hàng này? Thao tác này không thể hoàn tác." />

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            let editCustomerId = null;
            let deleteCustomerId = null;
            let profileCustomerId = null;
            const money = (n) => window.nhomNghin(n) + ' ₫';

            const openDrawer = (id) => $('#' + id).removeClass('translate-x-full');
            const closeDrawer = (id) => $('#' + id).addClass('translate-x-full');

            $('.close-customer-drawer').click(function() {
                closeDrawer('drawer-create-customer');
                closeDrawer('drawer-update-customer');
                closeDrawer('drawer-customer-profile');
            });

            window.reloadCustomerTable = function() {
                $.get('{{ route('customer.data') }}', { keyword: $('#search-customer').val() }, function(data) {
                    $('#customerTable').html(data);
                });
            };

            // Search with debounce
            $('#search-customer').on('input', window.debounce(function() {
                const val = $(this).val();
                if (val) $('#clearCustomerSearch').removeClass('hidden');
                else $('#clearCustomerSearch').addClass('hidden');
                window.reloadCustomerTable();
            }, 300));

            $('#clearCustomerSearch').on('click', function() {
                $('#search-customer').val('');
                $(this).addClass('hidden');
                window.reloadCustomerTable();
            });

            // Create Drawer
            $('#btn-open-create-customer').click(function() {
                $('#formAddCustomer')[0].reset();
                openDrawer('drawer-create-customer');
            });

            $('#formAddCustomer').submit(function(e) {
                e.preventDefault();
                window.submitFormWithProgress($(this), '{{ route('customer.add') }}', function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-create-customer');
                    window.reloadCustomerTable();
                });
            });

            // Edit Drawer
            $(document).on('click', '.editCustomerButton', function() {
                const c = $(this).data('item-customer');
                editCustomerId = c.id;
                $('#customer_name_edit').val(c.customer_name);
                $('#customer_phone_edit').val(c.customer_phone);
                $('#customer_email_edit').val(c.customer_email || '');
                $('#status_edit').val(c.status || 0);
                $('#province_edit').val(c.province || '');
                $('#ward_edit').val(c.ward || '');
                $('#address_edit').val(c.address || '');
                $('#note_edit').val(c.note || '');
                openDrawer('drawer-update-customer');
            });

            $('#formEditCustomer').submit(function(e) {
                e.preventDefault();
                if (!editCustomerId) return;
                window.submitFormWithProgress($(this), '/customer/edit/' + editCustomerId, function(response) {
                    window.showToast(response.success);
                    closeDrawer('drawer-update-customer');
                    window.reloadCustomerTable();
                });
            });

            // Delete Modal Trigger
            $(document).on('click', '.deleteCustomerButton', function() {
                deleteCustomerId = $(this).data('id-customer');
                const name = $(this).data('name-customer');
                $('#modal-delete-customer-msg').text(`Bạn có chắc muốn xóa khách hàng "${name}"?`);
                $('#modal-delete-customer').removeClass('hidden');
            });

            $('#modal-delete-customer-confirm').click(function() {
                if (!deleteCustomerId) return;
                $.ajax({
                    url: '/customer/delete/' + deleteCustomerId,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        window.showToast(response.success);
                        $('#modal-delete-customer').addClass('hidden');
                        window.reloadCustomerTable();
                    },
                    error: window.showAjaxError
                });
            });

            // Profile View
            $(document).on('click', '.viewCustomerButton', function() {
                profileCustomerId = $(this).data('id-customer');
                $('#profile-body').html('<div class="py-12 text-center text-slate-400 text-xs">Đang tải thông tin khách hàng...</div>');
                openDrawer('drawer-customer-profile');

                $.ajax({
                    url: '/customer/' + profileCustomerId + '/profile',
                    type: 'GET',
                    success: function(c) {
                        const rows = c.orders.length ? c.orders.map(o => `
                            <div class="flex items-center justify-between py-2.5 text-xs border-b border-slate-100 dark:border-slate-700">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">${o.order_code}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">${o.created_at} · ${o.status}</div>
                                </div>
                                <div class="font-bold text-slate-900 dark:text-white">${money(o.total_amount)}</div>
                            </div>
                        `).join('') : '<p class="py-6 text-xs text-center text-slate-400">Khách chưa có đơn hàng nào.</p>';

                        $('#profile-body').html(`
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-700 space-y-1">
                                <div class="text-base font-bold text-slate-900 dark:text-white">${c.customer_name}</div>
                                <div class="text-xs text-slate-600 dark:text-slate-300">📞 ${c.customer_phone} · ✉️ ${c.customer_email || 'Chưa có email'}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">📍 ${c.full_address || 'Chưa có địa chỉ'}</div>
                            </div>

                            <div class="grid grid-cols-3 gap-2.5">
                                <div class="p-3 text-center rounded-xl bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-950/30 dark:border-indigo-900">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Hạng</div>
                                    <div class="font-bold text-indigo-700 dark:text-indigo-300 text-sm mt-0.5">${c.tier}</div>
                                </div>
                                <div class="p-3 text-center rounded-xl bg-slate-50 border border-slate-200/80 dark:bg-slate-700/40 dark:border-slate-700">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Số đơn</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-sm mt-0.5">${c.order_count}</div>
                                </div>
                                <div class="p-3 text-center rounded-xl bg-slate-50 border border-slate-200/80 dark:bg-slate-700/40 dark:border-slate-700">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Tổng chi</div>
                                    <div class="font-bold text-emerald-600 text-sm mt-0.5">${money(c.total_spent)}</div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Lịch sử đơn hàng</h4>
                                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 bg-white dark:bg-slate-800">
                                    ${rows}
                                </div>
                            </div>
                        `);
                        $('#profile-note').val(c.note || '');
                    },
                    error: window.showAjaxError
                });
            });

            $('#btn-save-note').click(function() {
                if (!profileCustomerId) return;
                $.ajax({
                    url: '/customer/' + profileCustomerId + '/note',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { note: $('#profile-note').val() },
                    success: function(r) {
                        window.showToast(r.success);
                    },
                    error: window.showAjaxError
                });
            });
        });
    </script>
</x-app-layout>
or: function(xhr) {
                        showToast((xhr.responseJSON || {}).error || 'Không tải được hồ sơ khách hàng.', 'error');
                    }
                });
            });

            $('#btn-save-note').click(function() {
                if (!profileCustomerId) return;
                $.ajax({
                    url: '/customer/' + profileCustomerId + '/note',
                    type: 'POST',
                    data: {
                        note: $('#profile-note').val()
                    },
                    success: function(r) {
                        showToast(r.success);
                    },
                    error: function(xhr) {
                        showToast((xhr.responseJSON || {}).error || 'Không lưu được ghi chú.', 'error');
                    }
                });
            });
        });
    </script>
</x-app-layout>
