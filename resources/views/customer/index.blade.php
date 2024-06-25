<x-app-layout>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <nav class="flex mb-5" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-sm font-medium md:space-x-2">
                     <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}"
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
                                    aria-current="page">Customer</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">ALL CUSTOMERS</h1>
            </div>
            <div class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700">
                <div class="flex items-center mb-4 sm:mb-0">
                    <form class="sm:pr-3" action="#" method="GET">
                        <label for="products-search" class="sr-only">Search</label>
                        <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                            <input type="text" name="search" id="search-supplier"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for suppliers">
                        </div>
                    </form>
                    <div class="flex items-center w-full sm:justify-end">
                        <div class="flex pl-2 space-x-1">
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
                    Add new customer
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
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Name
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Phone
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Email
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Invoice Quantity
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Total Debt
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Total Payment
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="customerTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('customer.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{ $customers->withQueryString()->links('vendor.pagination.tailwind') }}

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
        <form id="formEdit" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-full">
                        <div id="btn_upload_edit" class="flex items-center justify-center w-full">
                            <label for="avatar_edit"
                                class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex items-center justify-center w-full h-full"> <!-- Thêm div này -->
                                    <img class="mt-3 rounded-lg w-28 h-28 sm:mb-0 xl:mb-4 2xl:mb-0"
                                        id="preview-image-edit" src="" alt="picture"
                                        style="object-fit: cover;"> <!-- Thêm style object-fit -->
                                </div>
                                <div id="svg_edit" class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span
                                            class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX.
                                        800x400px)</p>
                                </div>
                                <input id="avatar_edit" name="avatar" type="file" class="hidden" />
                            </label>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label for="customer_name_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <input type="text" name="customer_name" id="customer_name_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Type customer name" required="">
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label for="customer_phone_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                        <input type="text" name="customer_phone" id="customer_phone_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Type customer phone" required="">
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label for="customer_email_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" name="customer_email" id="customer_email_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Type customer email" required="">
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label for="status_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                        <select id="status_edit" name="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="0">Normal</option>
                            <option value="1">VIP</option>
                            <option value="1">Blocked</option>
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label for="address_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                        <textarea id="address_edit" rows="4" name="address"
                            class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Enter address here"></textarea>
                    </div>
                </div>
            </div>
            <div class="items-center p-6 border-t border-gray-200 rounded-b dark:border-gray-700">
                <div class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4">
                    <button type="submit"
                        class="text-white w-full justify-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Update
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

    <!-- Add Product Drawer -->
    <div id="drawer-create-product-default"
        class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            New Customer</h5>
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
                    <div id="btn_upload" class="flex items-center justify-center w-full">
                        <label for="avatar"
                            class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                            <div class="flex items-center justify-center w-full h-full"> <!-- Thêm div này -->
                                <img class="mt-3 rounded-lg w-28 h-28 sm:mb-0 xl:mb-4 2xl:mb-0" id="preview-image"
                                    src="" alt="picture" style="object-fit: cover;">
                                <!-- Thêm style object-fit -->
                            </div>
                            <div id="svg" class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span
                                        class="font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX.
                                    800x400px)</p>
                            </div>
                            <input id="avatar" name="avatar" type="file" class="hidden" />
                        </label>
                    </div>
                </div>
                <div>
                    <label for="customer_name"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="customer_name" id="customer_name"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Type customer name" required="">
                </div>
                <div>
                    <label for="customer_phone"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                    <input type="text" name="customer_phone" id="customer_phone"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Type customer phone" required="">
                </div>
                <div>
                    <label for="customer_email"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="customer_email" id="customer_email"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Type customer email" required="">
                </div>
                <div>
                    <label for="address"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                    <textarea id="address" rows="4" name="address"
                        class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Enter address here"></textarea>
                </div>
                <div class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4 md:absolute">
                    <button type="submit"
                        class="text-white w-full justify-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Add Customer
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
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- add supplier --}}
    <script>
        $(document).ready(function() {
            $('#formAdd').submit(function(e) {
                e.preventDefault(); // Ngăn chặn form submit theo cách truyền thống
                var formData = new FormData(this);
                $.ajax({
                    url: '{{ route('customer.add') }}', // URL được định nghĩa trong routes
                    type: 'POST',
                    data: formData,
                    contentType: false, // Quan trọng: không thiết lập kiểu nội dung
                    processData: false, // Quan trọng: không xử lý dữ liệu
                    success: function(response) {
                        // Xử lý khi thêm thành công
                        alert(response.success);

                        $('#closeDrawerAdd').click();

                        // Có thể làm mới danh sách suppliers hoặc reset form tại đây
                        $('form').find('input[type=text], input[type=file],textarea').val('');

                        $.ajax({
                            url: '{{ route('customer.data') }}', // Đường dẫn tới phương thức getSuppliers
                            type: 'GET',
                            success: function(data) {
                                $('#customerTable').html(
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
            $(document).on('click', '.deleteSupplierButton', function() {
                // Mở drawer
                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                const idCustomer = $(this).data('id-customer');
                const nameCustomer = $(this).data('name-customer');

                $('#contentDelete').html('Bạn có chắc chắn muốn xóa <strong>' + nameCustomer +
                    '</strong> không?');

                $(document).off('click', '#deleteBtn').on('click', '#deleteBtn', function() {

                    const url = '/customer/delete/' + idCustomer;

                    $.ajax({
                        url: url, // Sử dụng nối chuỗi để thêm idSupplier vào URL
                        type: 'GET',
                        success: function(response) {
                            // Xử lý khi xóa thành công
                            alert(response.success);

                            $('#closeDrawerDelete').click();

                            $.ajax({
                                url: '{{ route('customer.data') }}', // Đường dẫn tới phương thức getSuppliers
                                type: 'GET',
                                success: function(data) {
                                    $('#customerTable').html(
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
            $(document).on('click', '.editSupplierButton', function() {
                // Mở drawer
                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                $('form').find('input[type=text], input[type=file],textarea').val('');
                const customerData = $(this).data('item-customer');
                id = customerData.id;
                var newImageUrl;
                if (customerData.avatar != null) {
                    newImageUrl = customerData.avatar.replace("public", "storage");
                    $('#preview-image-edit').attr('src', newImageUrl);
                    $('#preview-image-edit').show();
                    $("#svg_edit").hide();
                } else {
                    $('#preview-image-edit').hide();
                    $("#svg_edit").show();
                }
                $('#customer_name_edit').val(customerData.customer_name);
                $('#customer_phone_edit').val(customerData.customer_phone);
                $('#customer_email_edit').val(customerData.customer_email);
                $('#address_edit').val(customerData.address);

                if (customerData.status == "1") {
                    $('#status_edit').val("1");
                } else if (customerData.status == "0") {
                    $('#status_edit').val("0");
                } else {
                    $('#status_edit').val("2");
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
            $('#formEdit').submit(function(e) {
                e.preventDefault(); // Ngăn chặn form submit theo cách truyền thống
                const urlEdit = '/customer/edit/' + id;
                var formData = new FormData(this);
                    $.ajax({
                        url: urlEdit, // URL được định nghĩa trong routes
                        type: 'POST',
                        data: formData,
                        contentType: false, // Quan trọng: không thiết lập kiểu nội dung
                        processData: false, // Quan trọng: không xử lý dữ liệu
                    success: function(response) {
                        // Xử lý khi thêm thành công
                        alert(response.success);

                        $('#closeDrawerEdit').click();

                        $.ajax({
                            url: '{{ route('customer.data') }}', // Đường dẫn tới phương thức getSuppliers
                            type: 'GET',
                            success: function(data) {
                                $('#customerTable').html(
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
            $('#search-supplier').on('input', function() {
                var keyword = $(this).val();

                $.ajax({
                    url: '{{ route('suppliers.search') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#customerTable').empty();
                        if (data.length > 0) {
                            $('#customerTable').html(data)
                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#customerTable').html();
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
    <script>
        $(document).ready(function() {
            // Lấy các phần tử DOM cần thiết
            var imageInput = $("#avatar");
            var previewImage = $("#preview-image");
            previewImage.hide();
            var btn_upload = $("#btn_upload");
            var svg = $("#svg");

            // Sự kiện click cho button "Choose image"
            btn_upload.on("click", function() {
                imageInput.click(); // Kích hoạt sự kiện click trên input type=file
            });

            // Sự kiện khi có thay đổi trong input type=file
            imageInput.on("change", function() {
                var file = this.files[0]; // Lấy file đầu tiên từ danh sách các file được chọn
                if (file) {
                    // Đọc file hình ảnh dưới dạng URL
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        // Hiển thị hình ảnh đã chọn lên thẻ <img>
                        svg.hide();
                        previewImage.show();
                        previewImage.attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file); // Đọc file dưới dạng URL Data
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Lấy các phần tử DOM cần thiết
            var imageInput = $("#avatar_edit");
            var previewImage = $("#preview-image-edit");
            previewImage.hide();
            var btn_upload = $("#btn_upload_edit");
            var svg = $("#svg_edit");

            // Sự kiện click cho button "Choose image"
            btn_upload.on("click", function() {
                imageInput.click(); // Kích hoạt sự kiện click trên input type=file
            });

            // Sự kiện khi có thay đổi trong input type=file
            imageInput.on("change", function() {
                var file = this.files[0]; // Lấy file đầu tiên từ danh sách các file được chọn
                if (file) {
                    // Đọc file hình ảnh dưới dạng URL
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        // Hiển thị hình ảnh đã chọn lên thẻ <img>
                        svg.hide();
                        previewImage.show();
                        previewImage.attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file); // Đọc file dưới dạng URL Data
                }
            });
        });
    </script>
</x-app-layout>
