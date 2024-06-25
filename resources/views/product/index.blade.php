<x-app-layout>
    <style>
        .selected {
            border: 4px solid green;
        }

        html,

        swiper-container {
            width: 100%;
            height: 100%;
        }

        swiper-slide {
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        swiper-slide img {
            display: block;
            width: 100%;
            height: 125px;
            object-fit: cover;
        }

        .append-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .append-buttons button {
            display: inline-block;
            cursor: pointer;
            border: 1px solid #007aff;
            color: #007aff;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 4px;
            margin: 0 10px;
            font-size: 13px;
        }
    </style>
    </style>

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
                                    aria-current="page">Products</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">ALL PRODUCTS</h1>
            </div>
            <div class="sm:flex">
                <div
                    class="items-center hidden mb-3 sm:flex sm:divide-x sm:divide-gray-100 sm:mb-0 dark:divide-gray-700">
                    <form class="sm:pr-3" action="#" method="GET">
                        <label for="products-search" class="sr-only">Search</label>
                        <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                            <input type="text" name="search" id="search-product"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for products">
                        </div>
                    </form>
                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">
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
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <button id="createProductButton"
                        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800"
                        type="button" data-drawer-target="drawer-create-product-default"
                        data-drawer-show="drawer-create-product-default" aria-controls="drawer-create-product-default"
                        data-drawer-placement="right">
                        Add new product
                    </button>
                    <a href="#"
                        class="inline-flex items-center justify-center w-1/2 px-3 py-2 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 sm:w-auto dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Export
                    </a>
                </div>
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
                                    PRODUCT NAME
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    PRICE
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    IMPORT PRICE
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    INVENTORY
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    SUPPLIER
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    CATEGORIES
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    UNIT
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    STATUS
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    ACTIONS
                                </th>
                            </tr>
                        </thead>
                        <tbody id="productTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('product.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{ $products->withQueryString()->links('vendor.pagination.tailwind') }}
    {{-- add --}}
    <div id="drawer-create-product-default"
        class="fixed top-0 right-0 z-40 w-1/2 h-screen max-w-3xl p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            New Product</h5>
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
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <div class="flex items-center justify-center w-full border border-gray-300 rounded-lg">
                            <label for="dropzone-file"
                                class="flex flex-col items-center justify-center w-full h-12 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
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
                                <input id="dropzone-file" type="file" name="images[]" class="hidden" multiple />
                            </label>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="product_name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PRODUCT
                            NAME</label>
                        <input type="text" name="product_name" id="product_name"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="import_price"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">IMPORT
                            PRICE</label>
                        <input type="number" name="import_price" id="import_price"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            min="0" placeholder="0" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="export_price"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EXPORT
                            PRICE</label>
                        <input type="number" name="sell_price" id="export_price"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            min="0" placeholder="0" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="unit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">UNIT</label>
                        <input type="text" name="unit" id="unit"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="supplier"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SUPPLIER</label>
                        <select id="supplier" name="supplier_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($supplier as $item)
                                <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="categories"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CATEGORY</label>
                        <select id="categories" name="categories_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($categories as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="countries"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product
                            Location</label>
                        <div class="flex divide-x">
                            <select id="zones" name="zone"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                @foreach ($zones as $item)
                                    <option value="{{ $item->zone }}">Kho {{ $item->zone }}</option>
                                @endforeach
                            </select>
                            <select id="shelves" name="shelf"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </select>
                            <select id="levels" name="level"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </select>
                        </div>
                    </div>
                </div>
                <swiper-container class="mySwiper" slides-per-view="5" space-between="10" id="image-preview"
                    free-mode="true">
                </swiper-container>
                <input id="choose-image" type="text" class="hidden" name="pin_image" />
            </div>
            <!-- Modal footer -->
            <div class="items-center p-6 border-t border-gray-200 rounded-b dark:border-gray-700">
                <button
                    class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                    type="submit">Add product</button>
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

    {{-- edit --}}
    <div id="drawer-update-product-default"
        class="drawer fixed top-0 right-0 z-40 w-1/2 h-screen max-w-3xl p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
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
        <form id="formEdit" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <div class="flex items-center justify-center w-full border border-gray-300 rounded-lg">
                            <label for="dropzone-file-edit"
                                class="flex flex-col items-center justify-center w-full h-12 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
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
                                <input id="dropzone-file-edit" type="file" name="images[]" class="hidden"
                                    multiple />
                            </label>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="product_name_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PRODUCT
                            NAME</label>
                        <input type="text" name="product_name" id="product_name_edit"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="import_price_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">IMPORT
                            PRICE</label>
                        <input type="number" name="import_price" id="import_price_edit"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            min="0" placeholder="0" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="export_price_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EXPORT
                            PRICE</label>
                        <input type="number" name="sell_price" id="export_price_edit"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            min="0" placeholder="0" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="unit_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">UNIT</label>
                        <input type="text" name="unit" id="unit_edit"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="supplier_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SUPPLIER</label>
                        <select id="supplier_edit" name="supplier_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($supplier as $item)
                                <option class="option-supplier" value="{{ $item->id }}">
                                    {{ $item->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="categories_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CATEGORY</label>
                        <select id="categories_edit" name="categories_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($categories as $item)
                                <option class="option-category" value="{{ $item->id }}">{{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="status_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">STATUS</label>
                        <select id="status_edit" name="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="2">Hết hàng</option>
                            <option value="1">Còn hàng</option>
                            <option value="0">Ngưng nhập/xuất</option>
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="old_location"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Old Location</label>
                        <p id="old_location"
                            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        </p>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="countries"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New
                            Location</label>
                        <div class="flex divide-x">
                            <select id="zones_edit" name="zone"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                @foreach ($zones as $item)
                                    <option value="{{ $item->zone }}">Kho {{ $item->zone }}</option>
                                @endforeach
                            </select>
                            <select id="shelves_edit" name="shelf"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </select>
                            <select id="levels_edit" name="level"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </select>
                        </div>
                    </div>
                </div>
                <swiper-container class="mySwiper" slides-per-view="5" space-between="10" id="image-preview-edit"
                    free-mode="true">
                </swiper-container>
                <hr>
                <swiper-container class="mySwiper" slides-per-view="5" space-between="10"
                    id="image-preview-edit-new" free-mode="true">
                </swiper-container>
                <input id="choose-image-edit" type="text" class="hidden" name="pin_image" />
            </div>
            <!-- Modal footer -->
            <div class="items-center p-6 border-t border-gray-200 rounded-b dark:border-gray-700">
                <button
                    class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                    type="submit">Update Product</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>

    <script>
        $(document).ready(function() {

            const reloadDataTable = () => {
                $.ajax({
                    url: '{{ route('product.data') }}',
                    type: 'GET',
                    success: function(data) {
                        $('#productTable').html(
                            data); // Cập nhật nội dung của bảng
                    }
                });
            }

            const uploadImages = (preview, pinImage, files) => {
                $.each(files, function(index, file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var imageUrl = e.target.result;
                        var imgContainer = $('<swiper-slide>').addClass(
                            'slide relative border border-gray-300'
                        );
                        var img = $('<img>').attr('src', imageUrl);

                        var deleteBtn = $('<img>').addClass(
                                'absolute top-0 right-0 m-2 w-6 h-6 cursor-pointer')
                            .attr('src',
                                'https://static.vecteezy.com/system/resources/previews/018/887/462/original/signs-close-icon-png.png'
                            );

                        deleteBtn.click(function() {
                            var parentSlide = $(this).parent();
                            var isSelected = parentSlide.hasClass(
                                'selected'
                            ); // Kiểm tra xem ảnh có đang được chọn làm ảnh ghim không
                            parentSlide.remove(); // Xóa ảnh

                            if (isSelected) {
                                pinImage.val(
                                    ''); // Reset giá trị của input nếu ảnh được ghim bị xóa
                            }
                        });

                        imgContainer.click(function() {
                            preview.find('swiper-slide').removeClass('selected');
                            $(this).addClass('selected');
                            preview.prepend($(this));
                            pinImage.empty();
                            pinImage.val(file.name);
                            $('.download').each(function() {
                                $(this).removeClass('selected');
                            });
                        });

                        imgContainer.append(img).append(deleteBtn);
                        preview.append(imgContainer);
                    };
                    reader.readAsDataURL(file);
                });
            }

            var inputFile = $('#dropzone-file');
            var preview = $('#image-preview');
            var pinImage = $('#choose-image');

            inputFile.on('change', function() {
                var files = inputFile.prop('files');
                uploadImages(preview, pinImage, files);
            });

            var inputFile_edit = $('#dropzone-file-edit');
            var preview_edit_new = $('#image-preview-edit-new');

            inputFile_edit.on('change', function() {
                var files_edit = inputFile_edit.prop('files');
                uploadImages(preview_edit_new, $('#choose-image-edit'), files_edit);
            })

            // add product
            $('#formAdd').submit(function(e) {
                e.preventDefault(); // Ngăn chặn form submit theo cách truyền thống
                var formData = new FormData(this);
                $.ajax({
                    url: '{{ route('product.add') }}', // URL được định nghĩa trong routes
                    type: 'POST',
                    data: formData,
                    contentType: false, // Quan trọng: không thiết lập kiểu nội dung
                    processData: false, // Quan trọng: không xử lý dữ liệu
                    success: function(response) {
                        // Xử lý khi thêm thành công
                        alert(response.success);

                        $('#closeDrawerAdd').click();

                        $('form').trigger('reset');

                        reloadDataTable();

                    },
                    error: function(xhr) {
                        // Xử lý lỗi
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });

            //edit product
            $(document).on('click', '.editProductButton', function() {
                // Mở drawer
                $('.download').remove();
                $('.slide').remove();
                $('form').trigger('reset');

                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                const product_id = $(this).data('id-product');
                console.log(product_id);

                var preview_edit = $('#image-preview-edit');
                var pinImage_edit = $('#choose-image-edit');

                $.ajax({
                    url: '/product/get-product/' + product_id,
                    type: 'GET',
                    success: function(response) {
                        $('#product_name_edit').val(response[0].product_name);
                        $('#inventory_edit').val(response[0].total_quantity);
                        $('#import_price_edit').val(response[0].import_price);
                        $('#export_price_edit').val(response[0].sell_price);
                        $('#unit_edit').val(response[0].unit);
                        $('#categories_edit').val(response[0].category.id);
                        $('#supplier_edit').val(response[0].supplier.id);
                        $('#status_edit').val(response[0].status);
                        if (response[0].location && response[0].location.code) {
                            $('#old_location').text(response[0].location.code);
                        } else {
                            $('#old_location').text(
                                'Không có vị trí'); // Hoặc bất kỳ giá trị mặc định nào phù hợp
                        }
                        $.each(response[0].product_image, function(index, image) {
                            if (image.is_pined) {
                                pinImage_edit.val(image.name);
                            } else {
                                pinImage_edit.val('');
                            }
                        });


                        $.each(response[0].product_image, function(index, image) {
                            if (image.is_pined) {
                                var imgContainer = $('<swiper-slide>').addClass(
                                    'download selected border border-gray-300'
                                );
                            } else {
                                var imgContainer = $('<swiper-slide>').addClass(
                                    'download border border-gray-300'
                                );
                            }

                            var img = $('<img>').attr('src', image.path.replace(
                                'public', 'storage'));

                            var deleteBtn = $('<img>').addClass(
                                    'absolute top-0 right-0 m-2 w-6 h-6 cursor-pointer'
                                )
                                .attr('src',
                                    'https://static.vecteezy.com/system/resources/previews/018/887/462/original/signs-close-icon-png.png'
                                );

                            deleteBtn.click(function() {
                                $.ajax({
                                    url: '/delete-image/' + image
                                        .id,
                                    type: 'GET',
                                    success: function(response) {
                                        // alert(response.success);
                                        reloadDataTable();
                                    },
                                    error: function(xhr, status,
                                        error) {
                                        // Xử lý lỗi
                                        alert('Có lỗi xảy ra: ' +
                                            error
                                        ); // Hiển thị thông báo lỗi
                                    }
                                });
                                var parentSlide = $(this).parent();
                                var isSelected = parentSlide.hasClass(
                                    'selected'
                                ); // Kiểm tra xem ảnh có đang được chọn làm ảnh ghim không
                                parentSlide.remove(); // Xóa ảnh

                                if (isSelected) {
                                    pinImage.val(
                                        ''
                                    ); // Reset giá trị của input nếu ảnh được ghim bị xóa
                                }
                            });

                            imgContainer.click(function() {
                                preview_edit.find('swiper-slide')
                                    .removeClass('selected');
                                $(this).addClass('selected');
                                preview_edit.prepend($(this));
                                pinImage_edit.empty();
                                pinImage_edit.val(image.name);
                                $('.slide').each(function() {
                                    $(this).removeClass('selected');
                                });
                            });

                            imgContainer.append(img).append(deleteBtn);
                            preview_edit.append(imgContainer);
                        });

                    },
                    error: function(xhr) {
                        // Xử lý lỗi
                        alert('Error: ' + xhr.statusText);
                    }
                });


                $('#formEdit').submit(function(event) {
                    // Ngăn chặn hành động mặc định của form
                    event.preventDefault();
                    const urlEdit = '/product/edit/' + product_id;
                    // Thay đổi action của form
                    $(this).attr('action', urlEdit);

                    // Submit form
                    this.submit();
                });

                // Sử dụng Tailwind CSS classes để hiển thị drawer
                drawerElement.removeClass('translate-x-full').addClass('translate-x-0');
                drawerElement.attr('aria-hidden', 'false');

                // Sự kiện đóng drawer

                // Sự kiện đóng drawer khi click vào phần tử có thuộc tính data - drawer - dismiss hoặc data -
                //     drawer - hide
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


            // delete product
            $(document).ready(function() {
                // Sử dụng event delegation để gắn sự kiện click cho tất cả các nút hiện tại và tương lai
                $(document).on('click', '.editProductButton', function() {
                    // Mở drawer
                    const drawerId = $(this).data(
                        'drawer-target'); // Lấy ID của drawer từ thuộc tính data
                    const drawerElement = $('#' + drawerId);
                    const idProduct = $(this).data('id-product');
                    const nameProduct = $(this).data('name-product');

                    $('#contentDelete').html('Bạn có chắc chắn muốn xóa <strong>' + nameProduct +
                        '</strong> không?');

                    $(document).off('click', '#deleteBtn').on('click', '#deleteBtn', function() {

                        const url = '/product/delete/' + idProduct;

                        $.ajax({
                            url: url, // Sử dụng nối chuỗi để thêm idSupplier vào URL
                            type: 'GET',
                            success: function(response) {
                                // Xử lý khi xóa thành công
                                alert(response.success);

                                $('#closeDrawerDelete').click();

                                reloadDataTable();
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
                    $(document).on('click', '[data-drawer-dismiss], [data-drawer-hide]',
                        function() {
                            const drawerId = $(this).attr('data-drawer-dismiss') || $(this)
                                .attr(
                                    'data-drawer-hide');
                            const drawerElement = $('#' + drawerId);
                            drawerElement.addClass('translate-x-full').removeClass(
                                'translate-x-0');
                            drawerElement.attr('aria-hidden', 'true');
                        });

                    // Sự kiện đóng drawer khi click ra bên ngoài drawer
                    $(document).on('click', function(event) {
                        // Điều này giả định rằng tất cả các drawer của bạn có một class chung là `.drawer`
                        const $drawer = $(
                            '.drawer'
                        ); // Sửa đổi selector này để phù hợp với class của drawer của bạn

                        // Kiểm tra xem click có nằm ngoài drawer và không phải là nút mở drawer
                        if (!$drawer.is(event.target) && $drawer.has(event.target)
                            .length === 0 && !$(
                                event.target).closest('[data-drawer-target]').length) {
                            $drawer.addClass('translate-x-full').removeClass(
                                'translate-x-0');
                            $drawer.attr('aria-hidden', 'true');
                        }
                    });
                });
            });



            $('#closeDrawerAdd').click(function() {
                $('.slide').remove();
                $('form').find('input[type=text],input[type=number],input[type=file] ').val('');
            })


            //search

            $('#search-product').on('input', function() {
                var keyword = $(this).val();

                $.ajax({
                    url: '{{ route('product.search') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#productTable').empty();
                        if (data.length > 0) {
                            $('#productTable').html(data)
                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#productTable').html();
                        }
                    }
                });
            });

        })
    </script>
    {{-- location add --}}
    <script>
        $(document).ready(function() {
            filterZone($('#zones').val(), $('#shelves'));
            filterZone($('#zones_edit').val(), $('#shelves_edit'));
            $(document).on('click', '#createProductButton', function() {
                filterShelf($('#shelves').val(), $('#levels'));
            });

            $(document).on('click', '.editProductButton', function() {
                filterShelf($('#shelves_edit').val(), $('#levels_edit'));
            });

            $('#createLocation').click(function() {
                $.ajax({
                    url: '{{ route('location.create') }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        filterZone(response.location.zone);
                        filterShelf(response.location.shelf);
                        $('#zones').val(response.location.zone);
                        $('#shelves').val(response.location.shelf);
                    },
                    error: function(response) {
                        alert(response.message); // Hiển thị thông báo lỗi
                    }
                });
            });

            function filterZone(val, shelveSelect) {
                $.ajax({
                    url: '{{ route('location.getShelf') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        zone: val
                    },
                    success: function(data) {
                        shelveSelect
                            .empty(); // Giả sử bạn muốn cập nhật một element với id `someElement`
                        data.shelves.forEach(function(shelves) {
                            shelveSelect.append($('<option>', {
                                value: shelves.shelf,
                                text: 'Giá ' + shelves.shelf
                            }));
                        });
                    }
                });
            }

            function filterShelf(val, levelSelect) {
                $.ajax({
                    url: '{{ route('location.getLevel') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        shelf: val
                    },
                    success: function(data) {
                        if (levelSelect.length) { // Kiểm tra xem phần tử có tồn tại không
                            levelSelect.empty();
                        } else {
                            console.log('Phần tử #levels không tồn tại.');
                        }
                        data.levels.forEach(function(levels) {
                            levelSelect.append($('<option>', {
                                value: levels.level,
                                text: 'Tầng ' + levels.level
                            }));
                        });
                    }
                });
            }

            $('#zones').on('change', function() {
                var val = $(this).val();
                filterZone(val, $('#shelves'))
            });

            $('#shelves').on('change', function() {
                var val = $(this).val();
                filterShelf(val, $('#levels'))
            });

            $('#zones_edit').on('change', function() {
                var val = $(this).val();
                filterZone(val, $('#shelves_edit'))
            });

            $('#shelves_edit').on('change', function() {
                var val = $(this).val();
                filterShelf(val, $('#levels_edit'))
            });
        });
    </script>


</x-app-layout>
