<x-app-layout>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <nav class="flex mb-5" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-sm font-medium md:space-x-2">
                        <li class="inline-flex items-center">
                            <a href="#"
                                class="inline-flex items-center text-gray-700 hover:text-primary-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                    </path>
                                </svg>
                                Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-400 md:ml-2 dark:text-gray-500"
                                    aria-current="page">Categories</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">ALL CATEGORIES</h1>
            </div>
            <div class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700">
                <div class="flex items-center mb-4 sm:mb-0">
                    <form class="sm:pr-3" action="#" method="GET">
                        <label for="products-search" class="sr-only">Search</label>
                        <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                            <input type="text" name="email" id="search-categories"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for categories">
                        </div>
                    </form>
                    <div class="flex items-center w-full sm:justify-end">
                        <div class="flex pl-2 space-x-1">
                            <a href="#"
                                class="inline-flex justify-center p-1 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </a>
                            <a href="#"
                                class="inline-flex justify-center p-1 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </a>
                            <a href="#"
                                class="inline-flex justify-center p-1 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </a>
                            <a href="#"
                                class="inline-flex justify-center p-1 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <button id="createProductButton"
                    class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800"
                    type="button" data-drawer-target="drawer-create-product-default"
                    data-drawer-show="drawer-create-product-default" aria-controls="drawer-create-product-default"
                    data-drawer-placement="right">
                    {{-- <a href="{{ route('categories.add') }}" class='btn btn-primary float-end'>Thêm mới</a> --}}
                    Add new category
                </button>
            </div>
        </div>
    </div>
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input id="checkbox-all" aria-describedby="checkbox-1" type="checkbox"
                                            class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="checkbox-all" class="sr-only">checkbox</label>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    ID
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Category Name
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Actions
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                </th>
                            </tr>
                        </thead>
                        {{-- itemmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm --}}
                        <tbody id="categoriesTable"
                            data-active-classes="bg-blue-100 dark:bg-gray-800 text-blue-600 dark:text-white"
                            data-accordion="open"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('categories.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{ $categories->withQueryString()->links('vendor.pagination.tailwind') }}

    <!-- Edit Product Drawer -->
    <div id="drawer-update-product-default"
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Update Product</h5>
        <button type="button" data-drawer-dismiss="drawer-update-product-default" id="closeDrawerEdit"
            aria-controls="drawer-update-product-default"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
        <form id="formEdit">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name_edit"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Type supplier name" required="">
                </div>
                <div>
                    <label for="status_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                    <select id="status_edit" name="status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="1">Sử dụng</option>
                        <option value="0">Ngưng sử dụng</option>
                    </select>
                </div>
                <div
                    class="bottom-0 left-0 flex justify-center w-full pb-4 mt-4 space-x-4 sm:absolute sm:px-4 sm:mt-0">
                    <button type="submit"
                        class="w-full justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Update
                    </button>
                    <button type="button" data-drawer-dismiss="drawer-update-product-default"
                        aria-controls="drawer-update-product-default"
                        class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                        <svg aria-hidden="true" class="w-5 h-5 -ml-1 sm:mr-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>


    <!-- Delete Product Drawer -->
    <div id="drawer-delete-product-default"
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            Delete item</h5>
        <button type="button" id="closeDrawerDelete" data-drawer-dismiss="drawer-delete-product-default"
            aria-controls="drawer-delete-product-default"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
        <svg class="w-10 h-10 mt-8 mb-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 id="contentDelete" class="mb-6 text-lg text-gray-500 dark:text-gray-400"></h3>
        <a href="#" id="deleteBtn"
            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-red-900">
            Yes, I'm sure
        </a>
        <a href="#"
            class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
            data-drawer-hide="drawer-delete-product-default">
            No, cancel
        </a>
    </div>



    <!-- Add Categories Drawer -->
    <div id="drawer-create-product-default"
        class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            New Category</h5>
        <button type="button" id="closeDrawerAdd" data-drawer-dismiss="drawer-create-product-default"
            aria-controls="drawer-create-product-default"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
        <form id="formAdd" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Category name" required="">
                </div>
                <div class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4 md:absolute">
                    <button type="submit" id="addCategories"
                        class="text-white w-full justify-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Add category
                    </button>
                    <button type="button" data-drawer-dismiss="drawer-create-product-default"
                        aria-controls="drawer-create-product-default"
                        class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                        <svg aria-hidden="true" class="w-5 h-5 -ml-1 sm:mr-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    {{-- add categories --}}
    <script>
        $(document).ready(function() {
            $('#formAdd').submit(function(e) {
                e.preventDefault(); // Ngăn chặn form submit theo cách truyền thống

                $.ajax({
                    url: '{{ route('categories.add') }}', // URL được định nghĩa trong routes
                    type: 'POST',
                    data: $(this).serialize(), // Serialize dữ liệu form
                    success: function(response) {
                        // Xử lý khi thêm thành công
                        alert(response.success);

                        $('#closeDrawerAdd').click();

                        // Có thể làm mới danh sách categories hoặc reset form tại đây
                        $('form').find('input[type=text], textarea').val('');

                        $.ajax({
                            url: '{{ route('categories.data') }}', // Đường dẫn tới phương thức getcategories
                            type: 'GET',
                            success: function(data) {
                                $('#categoriesTable').html(
                                    data); // Cập nhật nội dung của bảng
                            }
                        });
                    },
                    error: function(xhr) {
                        // Xử lý lỗi
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });
        });
    </script>
    {{-- delete supplier --}}
    <script>
        $(document).ready(function() {
            // Sử dụng event delegation để gắn sự kiện click cho tất cả các nút hiện tại và tương lai
            $(document).on('click', '.deleteCategoriesButton', function() {
                // Mở drawer
                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                const idCategories = $(this).data('id-categories');
                const nameSupplier = $(this).data('name-categories');

                $('#contentDelete').html('Bạn có chắc chắn muốn xóa <strong>' + nameSupplier +
                    '</strong> không?');

                $(document).off('click', '#deleteBtn').on('click', '#deleteBtn', function() {

                    const url = '/categories/delete/' + idCategories;

                    $.ajax({
                        url: url, // Sử dụng nối chuỗi để thêm idCategories vào URL
                        type: 'GET',
                        success: function(response) {
                            // Xử lý khi xóa thành công
                            alert(response.success);

                            $('#closeDrawerDelete').click();

                            $.ajax({
                                url: '{{ route('categories.data') }}', // Đường dẫn tới phương thức getSuppliers
                                type: 'GET',
                                success: function(data) {
                                    $('#categoriesTable').html(
                                        data); // Cập nhật nội dung của bảng
                                }
                            });
                        },
                        error: function(xhr) {
                            // Xử lý lỗi
                            alert('Error: ' + xhr.statusText);
                        }
                    });
                });

                // Sử dụng Tailwind CSS classes để hiển thị drawer
                drawerElement.removeClass('translate-x-full').addClass('translate-x-0');
                drawerElement.attr('aria-hidden', 'false');
            });
            // Sự kiện đóng drawer
            $(document).ready(function() {
                // Sự kiện đóng drawer khi click vào phần tử có thuộc tính data-drawer-dismiss hoặc data-drawer-hide
                $(document).on('click', '[data-drawer-dismiss], [data-drawer-hide]', function() {
                    const drawerId = $(this).attr('data-drawer-dismiss') || $(this).attr(
                        'data-drawer-hide');
                    const drawerElement = $('#' + drawerId);
                    drawerElement.addClass('translate-x-full').removeClass('translate-x-0');
                    drawerElement.attr('aria-hidden', 'true');
                });

                // Sự kiện đóng drawer khi click ra bên ngoài drawer
                $(document).on('click', function(event) {
                    // Điều này giả định rằng tất cả các drawer của bạn có một class chung là `.drawer`
                    const $drawer = $(
                        '.drawer'); // Sửa đổi selector này để phù hợp với class của drawer của bạn

                    // Kiểm tra xem click có nằm ngoài drawer và không phải là nút mở drawer
                    if (!$drawer.is(event.target) && $drawer.has(event.target).length === 0 && !$(
                            event.target).closest('[data-drawer-target]').length) {
                        $drawer.addClass('translate-x-full').removeClass('translate-x-0');
                        $drawer.attr('aria-hidden', 'true');
                    }
                });
            });
        });
    </script>
    {{-- edit supplier --}}
    <script>
        $(document).ready(function() {
            // Sử dụng event delegation để gắn sự kiện click cho tất cả các nút hiện tại và tương lai
            var id = 0;
            $(document).on('click', '.editCategoriesButton', function() {
                // Mở drawer
                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                const idCategories = $(this).data('id-categories');
                const nameCategories = $(this).data('name-categories');
                const statusCategories = $(this).data('status-categories');
                const url = '/categories/getCategories/' + idCategories;
                id = idCategories;

                $('form').find('input[type=text], textarea').val('');

                $('#name_edit').val(nameCategories);

                // Gán giá trị cho select
                if (statusCategories == "1") {
                    $('#status_edit').val("1");
                } else {
                    $('#status_edit').val("0");
                }

                // Sử dụng Tailwind CSS classes để hiển thị drawer
                drawerElement.removeClass('translate-x-full').addClass('translate-x-0');
                drawerElement.attr('aria-hidden', 'false');
            });
            // Sự kiện đóng drawer
            $(document).ready(function() {
                // Sự kiện đóng drawer khi click vào phần tử có thuộc tính data-drawer-dismiss hoặc data-drawer-hide
                $(document).on('click', '[data-drawer-dismiss], [data-drawer-hide]', function() {
                    const drawerId = $(this).attr('data-drawer-dismiss') || $(this).attr(
                        'data-drawer-hide');
                    const drawerElement = $('#' + drawerId);
                    drawerElement.addClass('translate-x-full').removeClass('translate-x-0');
                    drawerElement.attr('aria-hidden', 'true');
                });

                //Sự kiện đóng drawer khi click ra bên ngoài drawer
                $(document).on('click', function(event) {
                    // Điều này giả định rằng tất cả các drawer của bạn có một class chung là `.drawer`
                    const $drawer = $(
                        '.drawer'); // Sửa đổi selector này để phù hợp với class của drawer của bạn

                    // Kiểm tra xem click có nằm ngoài drawer và không phải là nút mở drawer
                    if (!$drawer.is(event.target) && $drawer.has(event.target).length === 0 && !$(
                            event.target).closest('[data-drawer-target]').length) {
                        $drawer.addClass('translate-x-full').removeClass('translate-x-0');
                        $drawer.attr('aria-hidden', 'true');
                    }
                });
            });
            $('#formEdit').submit(function(e) {
                e.preventDefault(); // Ngăn chặn form submit theo cách truyền thống
                const urlEdit = '/categories/edit/' + id;
                $.ajax({
                    url: urlEdit, // URL được định nghĩa trong routes
                    type: 'POST',
                    data: $(this).serialize(), // Serialize dữ liệu form
                    success: function(response) {
                        // Xử lý khi thêm thành công
                        alert(response.success);

                        $('#closeDrawerEdit').click();

                        // Có thể làm mới danh sách suppliers hoặc reset form tại đây
                        $('form').find('input[type=text], textarea').val('');

                        $.ajax({
                            url: '{{ route('categories.data') }}', // Đường dẫn tới phương thức getSuppliers
                            type: 'GET',
                            success: function(data) {
                                $('#categoriesTable').html(
                                    data); // Cập nhật nội dung của bảng
                            }
                        });
                    },
                    error: function(xhr) {
                        // Xử lý lỗi
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });
        });
    </script>
    {{-- search --}}

    <script>
        $(document).ready(function() {
            $('#search-categories').on('input', function() {
                var keyword = $(this).val();

                $.ajax({
                    url: '{{ route('categories.search') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#categoriesTable').empty();
                        if (data.length > 0) {
                            $('#categoriesTable').html(data)
                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#categoriesTable').html();
                        }
                    }
                });
            });
        });
    </script>
    <script>
        $('#checkall').change(function() {
            // Lấy giá trị (checked hoặc không checked) của checkbox "checkall"
            var isChecked = $(this).prop('checked');
            // Set giá trị của tất cả các checkbox khác thành giá trị của checkbox "checkall"
            $('.checkitem').prop('checked', isChecked);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#closeDrawerAdd').click(function() {
                $('form').find('input[type=text] ').val('');
            })
        })
    </script>
</x-app-layout>
