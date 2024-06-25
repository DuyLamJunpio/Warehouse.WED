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
                                    aria-current="page">Invoice</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">ALL INVOICES</h1>
            </div>
            <div class="sm:flex">
                <div
                    class="items-center hidden mb-3 sm:flex sm:divide-x sm:divide-gray-100 sm:mb-0 dark:divide-gray-700">

                    <form class="sm:pr-3" action="#" method="GET">
                        <label for="products-search" class="sr-only">Search</label>
                        <div class="relative w-48 sm:w-64 xl:w-96">
                            <input type="text" name="search" id="search-invoice"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for products">
                        </div>
                    </form>


                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">

                        <form class="sm:pr-3">
                            <select id="filter"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected value="">Tất cả</option>
                                <option value="0">Hóa đơn nhập</option>
                                <option value="1">Hóa đơn xuất</option>
                            </select>
                        </form>

                    </div>

                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">

                        <form class="sm:pr-3">
                            <select id="filter_pay_status"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected value="">Tất cả</option>
                                <option value="0">Chưa thanh toán</option>
                                <option value="1">Đã thanh toán</option>
                                <option value="2">Quá hạn</option>
                                <option value="3">Đã xóa</option>
                            </select>
                        </form>

                    </div>
                </div>
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <button id="createInvoiceButton"
                        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800"
                        type="button" data-drawer-target="drawer-create-product-default"
                        data-drawer-show="drawer-create-product-default" aria-controls="drawer-create-product-default"
                        data-drawer-placement="right">
                        Add new invoice
                    </button>
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
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    INVOICE CODE
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    INVOICE CREATOR
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    PARTNER
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    TOTAL
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    CREATED ON
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    INVOICE TYPE
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
                        <tbody id="invoiceTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('invoice.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{ $invoices->withQueryString()->links('vendor.pagination.tailwind') }}
    {{-- add --}}
    <div id="drawer-create-product-default"
        class="fixed top-0 right-0 z-40 w-full h-screen max-w-3xl p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
        tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
            NEW INVOICE</h5>
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
                        <label for="invoice_type"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="0">Hóa đơn nhập</option>
                            <option value="1">Hóa đơn xuất</option>
                        </select>
                    </div>
                    <div id="supplierSelect" class="col-span-6 sm:col-span-3">
                        <label for="supplier"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Supplier Name</label>
                        @php
                            $supplierList = $supplier->where('status', 1)->all();
                        @endphp

                        <select id="supplier" name="supplier_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($supplierList as $item)
                                <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="customerSelect" class="col-span-6 sm:col-span-3" hidden>
                        <label for="customer"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer Name</label>
                        @php $customerList = $customer->where('status', 0)->all(); @endphp
                        <select id="customer" name="customer_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($customerList as $item)
                                <option value="{{ $item->id }}">{{ $item->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="invoice_creator"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Invoice
                            Creator</label>
                        <input type="text" id="user_id" name="user_id" hidden value="{{ Auth::user()->id }}">
                        <input type="text" id="invoice_creator"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            value="{{ Auth::user()->name }}" readonly>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="discount"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Discount(%)</label>
                        <input type="number" name="discount" id="discount" inputmode="numeric" pattern="[0-9]*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="0" min="0">
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="pay_status"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Payment Status</label>
                        <select id="pay_status" name="pay_status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="0">Chưa thanh toán</option>
                            <option value="1">Đã thanh toán</option>
                            <option value="2">Quá hạn</option>
                            <option value="3">Đã hủy</option>
                            <option value="4">Hoàn trả</option>
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="due_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Due
                            Date</label>
                        <input type="date" name="due_date" id="due_date"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                    </div>
                    <div class="col-span-6 sm:col-full">
                        {{-- <input type="text" name="products" id="jsonProducts" hidden> --}}
                        <label for="product_list"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Products List</label>
                        <!-- Products table or list here -->
                        <div class="overflow-hidden shadow">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Product</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Price</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Quantity</th>
                                        <th id="expiry_th" scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Expiry</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Amount</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableAdd"
                                    class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr>
                                        <th id="add_product" colspan="6">
                                            <button id="modalProduct" type="button"
                                                data-modal-target="default-modal" data-modal-toggle="default-modal"
                                                class="w-full text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800">Choose
                                                Products</button>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="col-span-6 sm:col-full mb-5">
                            <label for="notes"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                            <textarea id="notes" rows="4" name="note"
                                class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Enter note here"></textarea>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <label for="terms_and_conditions"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Terms and
                                Conditions</label>
                            <textarea id="terms_and_conditions" rows="4" name="term"
                                class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Enter terms and conditions here"></textarea>
                        </div>
                    </div>
                    <div
                        class="col-span-6 sm:col-span-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Taxable
                                        Amount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p id="taxable_amount"
                                        class="text-lg font-semibold text-gray-900 dark:text-white">0đ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label
                                        class="block text-sm font-medium text-gray-900 dark:text-white">Discount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p id="discountNumber"
                                        class="text-lg font-semibold text-gray-900 dark:text-white">0%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white">VAT</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">10%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label
                                        class="block text-lg leading-relaxed font-bold text-gray-900 dark:text-white">Total
                                        Amount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <input type="number" hidden id="total_amount_input" name="total_amount"
                                        style="color: black">
                                    <p id="total_amount"
                                        class="text-xl leading-relaxed font-bold text-gray-900 dark:text-white">0đ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full mb-2 mt-5">
                            <label for="signature_name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Signature
                                Name</label>
                            <input type="text" name="signature_name" id="signature_name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Type signature name" required="">
                        </div>

                        <div class="col-span-6 sm:col-full">
                            <div id="btn_upload" class="flex items-center justify-center w-full">
                                <label for="signature"
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                    <div class="flex items-center justify-center w-full h-full"> <!-- Thêm div này -->
                                        <img class="mt-3 rounded-lg w-28 h-28 sm:mb-0 xl:mb-4 2xl:mb-0"
                                            id="preview-image" src="" alt="picture"
                                            style="object-fit: cover;">
                                        <!-- Thêm style object-fit -->
                                    </div>
                                    <div id="svg" class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span
                                                class="font-semibold">Click to upload signature</span></p>
                                    </div>
                                </label>
                                <input id="signature" type="file" name="signature" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="submit"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <span>Save & Send</span>
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
            Delete Invoice</h5>
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
        class="drawer fixed top-0 right-0 z-40 w-full h-screen max-w-4xl p-4 overflow-y-auto transition-transform translate-x-full bg-white dark:bg-gray-800"
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
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <label for="invoice_type_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Invoice Type</label>
                        <select id="invoice_type_edit" name="invoice_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="0">Hóa đơn nhập</option>
                            <option value="1">Hóa đơn xuất</option>
                        </select>
                    </div>
                    <div id="supplierSelectEdit" class="col-span-6 sm:col-span-3">
                        <label for="supplier_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Supplier Name</label>
                        <select id="supplier_edit" name="supplier_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($supplierList as $item)
                                <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="customerSelectEdit" class="col-span-6 sm:col-span-3" hidden>
                        <label for="customer_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer Name</label>

                        <select id="customer_edit" name="customer_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach ($customerList as $item)
                                <option value="{{ $item->id }}">{{ $item->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="invoice_creator_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Invoice
                            Creator</label>
                        <input type="text" name="user_id" id="user_id_edit" hidden>
                        <input type="text" id="invoice_creator_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            readonly>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="discount_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Discount(%)</label>
                        <input type="number" name="discount" id="discount_edit" inputmode="numeric"
                            pattern="[0-9]*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="0" min="0">
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="pay_status_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Payment Status</label>
                        <select id="pay_status_edit" name="pay_status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="0">Chưa thanh toán</option>
                            <option value="1">Đã thanh toán</option>
                        </select>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="due_date_edit"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Due
                            Date</label>
                        <input type="date" name="due_date" id="due_date_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                    </div>
                    <div class="col-span-6 sm:col-full">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Products
                            List</label>
                        <!-- Products table or list here -->
                        <div class="overflow-hidden shadow">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Product</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Price</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Quantity</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Amount</th>
                                        <th scope="col"
                                            class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableEdit"
                                    class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr>
                                        <th colspan="5">
                                            <button id="modalProductEdit" type="button"
                                                data-modal-target="default-modal" data-modal-toggle="default-modal"
                                                class="w-full text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800">Choose
                                                Products</button>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="col-span-6 sm:col-full mb-5">
                            <label for="notes_edit"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                            <textarea id="notes_edit" rows="4" name="note"
                                class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Enter note here"></textarea>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <label for="terms_and_conditions_edit"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Terms and
                                Conditions</label>
                            <textarea id="terms_and_conditions_edit" rows="4" name="term"
                                class="block p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Enter terms and conditions here"></textarea>
                        </div>
                    </div>
                    <div
                        class="col-span-6 sm:col-span-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Taxable
                                        Amount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p id="taxable_amount_edit"
                                        class="text-lg font-semibold text-gray-900 dark:text-white">0đ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label
                                        class="block text-sm font-medium text-gray-900 dark:text-white">Discount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p id="discountNumberEdit"
                                        class="text-lg font-semibold text-gray-900 dark:text-white">0%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white">VAT</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">10%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3 flex items-center">
                                    <label
                                        class="block text-lg leading-relaxed font-bold text-gray-900 dark:text-white">Total
                                        Amount</label>
                                </div>
                                <div class="col-span-6 sm:col-span-3 flex items-center justify-end">
                                    <input type="number" hidden id="total_amount_input_edit" name="total_amount"
                                        style="color: black">
                                    <p id="total_amount_edit"
                                        class="text-xl leading-relaxed font-bold text-gray-900 dark:text-white">0đ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-full mb-2 mt-5">
                            <label for="signature_name_edit"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Signature
                                Name</label>
                            <input type="text" name="signature_name" id="signature_name_edit"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Type signature name" required="">
                        </div>

                        <div class="col-span-6 sm:col-full">
                            <div id="btn_upload_edit" class="flex items-center justify-center w-full">
                                <label for="signature_edit"
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                    <div class="flex items-center justify-center w-full h-full"> <!-- Thêm div này -->
                                        <img class="mt-3 rounded-lg w-28 h-28 sm:mb-0 xl:mb-4 2xl:mb-0"
                                            id="preview-image-edit" src="" alt="picture"
                                            style="object-fit: cover;">
                                        <!-- Thêm style object-fit -->
                                    </div>
                                </label>
                                <input id="signature_edit" type="file" name="signature" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="submit"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <span>Update</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- modal product list -->
    <div id="default-modal" tabindex="-1"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-7xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700"
                style="max-height: 90vh; overflow-y: auto;">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Product List
                    </h3>
                    <form id="formSearchProduct" class="sm:pr-3" action="#" method="GET" hidden>
                        <label for="products-search" class="sr-only">Search</label>
                        <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                            <input type="text" name="search" id="search-product"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for products">
                        </div>
                    </form>
                    <form id="formSearchSupplier" class="sm:pr-3" action="#" method="GET">
                        <label for="products-supplier" class="sr-only">Search</label>
                        <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                            <input type="text" name="search" id="search-supplier"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search for suppliers">
                        </div>
                    </form>
                    <div>
                        <select id="supplierFilter" name="partner_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option id="allSupplier" hidden value="">Tất cả</option>
                            @foreach ($supplierList as $item)
                                <option value="{{ $item->id }}">{{ $item->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="default-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="p-4">
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
                            </tr>
                        </thead>
                        <tbody id="tableProduct"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('invoice.list_product')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>


    {{-- addddddddddddddddddddddd --}}
    <script>
        $(document).ready(function() {
            // Lấy các phần tử DOM cần thiết
            var imageInput = $("#signature");
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
            $.ajax({
                url: '/invoice/get-product-supplier/' + $('#supplier').val(),
                type: 'GET',
                success: function(data) {
                    $('#tableProduct').html(
                        data); // Cập nhật nội dung của bảng
                }
            });

            $('#invoice_type').change(function() {
                if ($(this).val() == 0) {
                    var idSupplier = $('#supplier').val();
                    $.ajax({
                        url: '/invoice/get-product-supplier/' + idSupplier,
                        type: 'GET',
                        success: function(data) {
                            $('#tableProduct').html(
                                data); // Cập nhật nội dung của bảng
                        }
                    });
                    $('#supplierSelect').show();
                    $('#customerSelect').hide();
                    $('#allSupplier').hide();
                } else {
                    $('#allSupplier').show();
                    $.ajax({
                        url: '{{ route('invoice.getproduct') }}',
                        type: 'GET',
                        success: function(data) {
                            $('#tableProduct').html(
                                data); // Cập nhật nội dung của bảng
                        }
                    });
                    $('#supplierSelect').hide();
                    $('#customerSelect').show();
                }
            })
        });
    </script>

    <script>
        $('#createInvoiceButton').click(function() {
            $('#productTableAdd tr:not(:last)').remove();
            $('form').find('input[type=text], input[type=number], input[type=file]').not(
                '#invoice_creator, #user_id').val('');
        })
    </script>

    <script>
        $(document).on('click', '#modalProduct', function() {
            if ($('#invoice_type').val() == 1) {
                $('#supplierFilter').val('');
                $('#formSearchProduct').show();
                $('#formSearchSupplier').hide();
            } else {
                $('#supplierFilter').val($('#supplier').val());
                $('#formSearchProduct').hide();
                $('#formSearchSupplier').show();
            }
            $('#supplier').change(function() {
                $('#supplierFilter').val($(this).val());
                ajaxProductSupplier($(this).val());
            })

            $('#supplierFilter').change(function() {
                $('#supplier').val($(this).val());
                ajaxProductSupplier($(this).val());
            })

            function ajaxProductSupplier(value) {
                $.ajax({
                    url: '/invoice/get-product-supplier/' + value,
                    type: 'GET',
                    success: function(data) {
                        $('#tableProduct').html(
                            data); // Cập nhật nội dung của bảng
                        // Xóa trạng thái checked hiện tại
                        $('#tableProduct .checkitem').prop('checked', false);

                        // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                        var selectedIds = $('#productTableAdd tr').map(function() {
                            return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                        }).get();

                        // Duyệt qua từng checkbox trong bảng sản phẩm modal
                        $('#tableProduct .checkitem').each(function() {
                            var productId = $(this).closest('tr').attr('id');
                            // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                            if (selectedIds.indexOf(productId) !== -1) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                });
            }

            // Hàm debounce để tránh gửi yêu cầu liên tục
            function debounce(func, wait, immediate) {
                var timeout;
                return function() {
                    var context = this,
                        args = arguments;
                    var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            };

            // Hàm thực hiện tìm kiếm
            function search(keyword) {
                $.ajax({
                    url: '{{ route('invoice.search_product') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#tableProduct').empty();
                        if (data.length > 0) {
                            $('#tableProduct').html(data);

                            // Xóa trạng thái checked hiện tại
                            $('#tableProduct .checkitem').prop('checked', false);

                            // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                            var selectedIds = $('#productTableAdd tr').map(function() {
                                return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                            }).get();

                            // Duyệt qua từng checkbox trong bảng sản phẩm modal
                            $('#tableProduct .checkitem').each(function() {
                                var productId = $(this).closest('tr').attr('id');
                                // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                                if (selectedIds.indexOf(productId) !== -1) {
                                    $(this).prop('checked', true);
                                }
                            });

                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#tableProduct').html();
                        }
                    }
                });
            }

            // Sử dụng hàm debounce cho hàm tìm kiếm
            var debouncedSearch = debounce(function(event) {
                search(event.target.value);
            }, 250); // Chờ đợi 250ms sau khi người dùng ngừng nhập

            // Gắn sự kiện input vào input tìm kiếm
            $('#search-product').on('input', debouncedSearch);

            function searchSupplier(keyword) {
                $.ajax({
                    url: '{{ route('invoice.search_supplier') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#tableProduct').empty();
                        if (data.length > 0) {
                            $('#tableProduct').html(data);

                            // Xóa trạng thái checked hiện tại
                            $('#tableProduct .checkitem').prop('checked', false);

                            // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                            var selectedIds = $('#productTableAdd tr').map(function() {
                                return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                            }).get();

                            // Duyệt qua từng checkbox trong bảng sản phẩm modal
                            $('#tableProduct .checkitem').each(function() {
                                var productId = $(this).closest('tr').attr('id');
                                // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                                if (selectedIds.indexOf(productId) !== -1) {
                                    $(this).prop('checked', true);
                                }
                            });

                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#tableProduct').html();
                        }
                    }
                });
            }

            // Sử dụng hàm debounce cho hàm tìm kiếm
            var debouncedSearchSupplier = debounce(function(event) {
                searchSupplier(event.target.value);
            }, 250); // Chờ đợi 250ms sau khi người dùng ngừng nhập

            // Gắn sự kiện input vào input tìm kiếm
            $('#search-supplier').on('input', debouncedSearchSupplier);

        })
    </script>

    <script>
        function reloadData(value) {
            $.ajax({
                url: '/invoice/filter/' + value,
                type: 'GET',
                success: function(data) {
                    $('#invoiceTable').html(
                        data); // Cập nhật nội dung của bảng
                }
            });
        }

        $(document).on('change', '#filter', function() {
            reloadData($(this).val());
        })

        $(document).on('change', '#filter_pay_status', function() {
            $.ajax({
                url: '/invoice/filter-payment/' + $(this).val(),
                type: 'GET',
                success: function(data) {
                    $('#invoiceTable').html(
                        data); // Cập nhật nội dung của bảng
                }
            });
        })

        $(document).ready(function() {
            $('#formAdd').submit(function(e) {
                e.preventDefault(); // Ngăn không cho form gửi theo cách thông thường

                var formData = new FormData(this);
                $('#productTableAdd tr').slice(0, -1).each(function(index) {
                    var productId = $(this).find('.delete-product-button').data('productId');
                    var quantity = $(this).find('.quantity').val();
                    var expiry = $(this).find('.expiry').val();

                    if ($('#invoice_type').val() == 0) {
                        if (productId && quantity !== undefined && quantity !== '' && expiry !==
                            undefined && expiry !== '') {
                            formData.append('products[' + index + '][product_id]', productId);
                            formData.append('products[' + index + '][quantity]', quantity);
                            formData.append('products[' + index + '][expiry]', expiry);
                        }
                    } else {
                        if (productId && quantity !== undefined && quantity !== '') {
                            formData.append('products[' + index + '][product_id]', productId);
                            formData.append('products[' + index + '][quantity]', quantity);
                        }
                    }
                });

                $.ajax({
                    url: '{{ route('invoice.add') }}', // Thay đổi URL theo đường dẫn thực tế
                    type: 'POST',
                    data: formData,
                    processData: false, // Không xử lý dữ liệu
                    contentType: false, // Không đặt kiểu nội dung mặc định
                    success: function(response) {
                        alert(response.success);
                        $('#closeDrawerAdd').click();
                        $('#productTableAdd tr:not(:last)').remove();
                        $('form').find('input[type=text],input[type=number],input[type=file]')
                            .val('');
                        reloadData('');
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });
        });
    </script>

    {{-- edittttttttttttttt --}}

    <script>
        $(document).ready(function() {
            // Lấy các phần tử DOM cần thiết
            var imageInput = $("#signature_edit");
            var previewImage = $("#preview-image-edit");
            var btn_upload = $("#btn_upload_edit");

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
                        previewImage.attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file); // Đọc file dưới dạng URL Data
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#invoice_type_edit').change(function() {
                if ($(this).val() == 0) {
                    var idSupplier = $('#supplier').val();
                    $.ajax({
                        url: '/invoice/get-product-supplier/' + idSupplier,
                        type: 'GET',
                        success: function(data) {
                            $('#tableProduct').html(
                                data); // Cập nhật nội dung của bảng
                        }
                    });
                    $('#supplierSelectEdit').show();
                    $('#customerSelectEdit').hide();
                    $('#allSupplier').hide();
                } else {
                    $('#allSupplier').show();
                    $.ajax({
                        url: '{{ route('invoice.getproduct') }}',
                        type: 'GET',
                        success: function(data) {
                            $('#tableProduct').html(
                                data); // Cập nhật nội dung của bảng
                        }
                    });
                    $('#supplierSelectEdit').hide();
                    $('#customerSelectEdit').show();
                }
            })
        });
    </script>

    <script>
        $(document).on('click', '#modalProductEdit', function() {
            if ($('#invoice_type_edit').val() == 1) {
                $('#supplierFilter').val('');
                $('#formSearchProduct').show();
                $('#formSearchSupplier').hide();
            } else {
                $('#supplierFilter').val($('#supplier_edit').val());
                $('#formSearchProduct').hide();
                $('#formSearchSupplier').show();
            }
            $('#supplier_edit').change(function() {
                $('#supplierFilter').val($(this).val());
                ajaxProductSupplier($(this).val());
            })

            $('#supplierFilter').change(function() {
                $('#supplier_edit').val($(this).val());
                ajaxProductSupplier($(this).val());
            })

            function ajaxProductSupplier(value) {
                $.ajax({
                    url: '/invoice/get-product-supplier/' + value,
                    type: 'GET',
                    success: function(data) {
                        $('#tableProduct').html(
                            data); // Cập nhật nội dung của bảng
                        // Xóa trạng thái checked hiện tại
                        $('#tableProduct .checkitem').prop('checked', false);

                        // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                        var selectedIds = $('#productTableEdit tr').map(function() {
                            return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                        }).get();

                        // Duyệt qua từng checkbox trong bảng sản phẩm modal
                        $('#tableProduct .checkitem').each(function() {
                            var productId = $(this).closest('tr').attr('id');
                            // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                            if (selectedIds.indexOf(productId) !== -1) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                });
            }

            // Hàm debounce để tránh gửi yêu cầu liên tục
            function debounce(func, wait, immediate) {
                var timeout;
                return function() {
                    var context = this,
                        args = arguments;
                    var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            };

            // Hàm thực hiện tìm kiếm
            function search(keyword) {
                $.ajax({
                    url: '{{ route('invoice.search_product') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#tableProduct').empty();
                        if (data.length > 0) {
                            $('#tableProduct').html(data);

                            // Xóa trạng thái checked hiện tại
                            $('#tableProduct .checkitem').prop('checked', false);

                            // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                            var selectedIds = $('#productTableEdit tr').map(function() {
                                return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                            }).get();

                            // Duyệt qua từng checkbox trong bảng sản phẩm modal
                            $('#tableProduct .checkitem').each(function() {
                                var productId = $(this).closest('tr').attr('id');
                                // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                                if (selectedIds.indexOf(productId) !== -1) {
                                    $(this).prop('checked', true);
                                }
                            });

                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#tableProduct').html();
                        }
                    }
                });
            }

            // Sử dụng hàm debounce cho hàm tìm kiếm
            var debouncedSearch = debounce(function(event) {
                search(event.target.value);
            }, 250); // Chờ đợi 250ms sau khi người dùng ngừng nhập

            // Gắn sự kiện input vào input tìm kiếm
            $('#search-product').on('input', debouncedSearch);

            function searchSupplier(keyword) {
                $.ajax({
                    url: '{{ route('invoice.search_supplier') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $('#tableProduct').empty();
                        if (data.length > 0) {
                            $('#tableProduct').html(data);

                            // Xóa trạng thái checked hiện tại
                            $('#tableProduct .checkitem').prop('checked', false);

                            // Lấy tất cả các ID sản phẩm từ bảng productTableAdd
                            var selectedIds = $('#productTableEdit tr').map(function() {
                                return this.id; // Giả sử mỗi <tr> có id là ID của sản phẩm
                            }).get();

                            // Duyệt qua từng checkbox trong bảng sản phẩm modal
                            $('#tableProduct .checkitem').each(function() {
                                var productId = $(this).closest('tr').attr('id');
                                // Kiểm tra nếu ID sản phẩm nằm trong danh sách đã chọn
                                if (selectedIds.indexOf(productId) !== -1) {
                                    $(this).prop('checked', true);
                                }
                            });

                        } else {
                            // Hiển thị thông báo không tìm thấy kết quả
                            $('#tableProduct').html();
                        }
                    }
                });
            }

            // Sử dụng hàm debounce cho hàm tìm kiếm
            var debouncedSearchSupplier = debounce(function(event) {
                searchSupplier(event.target.value);
            }, 250); // Chờ đợi 250ms sau khi người dùng ngừng nhập

            // Gắn sự kiện input vào input tìm kiếm
            $('#search-supplier').on('input', debouncedSearchSupplier);

        })
    </script>

    <script>
        function reloadData(value) {
            $.ajax({
                url: '/invoice/filter/' + value,
                type: 'GET',
                success: function(data) {
                    $('#invoiceTable').html(
                        data); // Cập nhật nội dung của bảng
                }
            });
        }

        $(document).on('change', '#filter', function() {
            reloadData($(this).val());
        })

        $(document).on('click', '.editInvoiceButton', function() {
            $('#productTableEdit tr:not(:last)').remove();
            $('form').find('input[type=text], input[type=number], input[type=file]').val('');


            const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
            const drawerElement = $('#' + drawerId);
            const invoice_id = $(this).data('id-invoice');

            $.ajax({
                url: '/invoice/get-invoice-id/' + invoice_id,
                type: 'GET',
                success: function(response) {
                    // Gán giá trị cho select
                    if (response.invoice_type == "1") {
                        $('#invoice_type_edit').val("1");
                        $('#customer_edit').val(response.customer_id);
                        $('#supplierSelectEdit').hide();
                        $('#customerSelectEdit').show();
                    } else {
                        $('#invoice_type_edit').val("0");
                        $('#supplier_edit').val(response.supplier_id);
                        $('#supplierSelectEdit').show();
                        $('#customerSelectEdit').hide();
                    }

                    $('#user_id_edit').val(response.user_id);
                    $('#invoice_creator_edit').val(response.invoice_creator);
                    if (response.discount == null) {
                        $('#discount_edit').val(0);
                        $('#discountNumberEdit').text('0%');
                    } else {
                        $('#discountNumberEdit').text(response.discount + '%');
                        $('#discount_edit').val(response.discount);
                    }
                    $('#discount_edit').val(response.discount);
                    $('#pay_status_edit').val(response.pay_status);
                    $('#due_date_edit').val(response.due_date);
                    $('#notes_edit').text(response.note);
                    $('#terms_and_conditions_edit').text(response.term);
                    $('#discountNumberEdit').text(response.discount + '%');
                    $('#total_amount_edit').text(formatPrice(response.total_amount));
                    $('#signature_name_edit').val(response.signature_name);
                    $('#preview-image-edit').attr("src", response.signature.replace('public',
                        'storage'));

                    for (let index = 0; index < response.product_invoices.length; index++) {
                        const element = response.product_invoices[index];

                        const imagePath = element.product.image_model.find(item => item.is_pined === 1)
                            ?.path.replace('public', 'storage') ||
                            (element.product.image_model.length > 0 ? element.product.image_model[0]
                                .path.replace('public', 'storage') :
                                'https://static.vecteezy.com/system/resources/previews/004/141/669/original/no-photo-or-blank-image-icon-loading-images-or-missing-image-mark-image-not-available-or-image-coming-soon-sign-simple-nature-silhouette-in-frame-isolated-illustration-vector.jpg'
                            );

                        const row = `
                        <tr id="${element.product.id}" class="hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="flex items-center p-4 mr-12 space-x-6 whitespace-nowrap">
                                <img class="w-10 h-10 rounded-lg" src="${imagePath}">
                                <div class="text-sm font-normal text-gray-500 dark:text-gray-4000">
                                    <div class="text-base font-semibold text-gray-900 dark:text-white">${element.product.product_name}</div>
                                    <div class="text-xs font-normal text-gray-500 dark:text-gray-400">${ element.product.location ? element.product.location.code : 'Không có vị trí' }</div>
                                </div>
                            </td>
                            <td class="cost p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">${formatPrice(element.product.import_price)}</td>
                            <td class="price p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white" hidden>${formatPrice(element.product.sell_price)}</td>
                            <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <div class="flex">
                                    <input type="number" inputmode="numeric" data-product-id="${element.product.id}"
                                    class="quantity rounded-none rounded-e-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="0" required min="0" value="${element.quantity}">
                                    <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    <span style="margin-right: 5px;">Tồn kho:</span><strong class="inventory">${element.product.expiries_sum_quantity_exp ?? 0}</strong>
                                    </span>
                                </div>
                            </td>
                            <td class="total p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">${response.invoice_type == 0 ? formatPrice(element.product.import_price * element.quantity) : formatPrice(element.product.sell_price * element.quantity)}</td>
                            <td class="p-4">
                                <button type="button" data-product-id="${element.product.id}"
                                class="delete-product-button inline-flex items-center px-2 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd">
                                    </path>
                                </svg>
                                </button>
                            </td>
                        </tr>`;

                        $('#productTableEdit').prepend(row);
                    }

                    updateTotalAmountEdit();

                },
                error: function(xhr) {
                    // Xử lý lỗi
                    alert('Error: ' + xhr.statusText);
                }
            });

            function formatPrice(price) {
                // Chuyển đổi giá trị price thành số, loại bỏ các dấu chấm nếu có
                var numericPrice = parseInt(price.toString().replace(/\./g, '').replace('₫', '').trim());
                // Kiểm tra xem numericPrice có phải là số hợp lệ không
                if (!isNaN(numericPrice)) {
                    // Định dạng số theo chuẩn Việt Nam và trả về
                    return numericPrice.toLocaleString('vi-VN', {
                        style: 'currency',
                        currency: 'VND'
                    });
                } else {
                    // Trả về chuỗi rỗng hoặc giá trị mặc định nếu price không phải là số
                    return '';
                }
            }

            function updateTotalAmountEdit() {
                let taxableAmount = 0;
                let totalAmount = 0;
                $('.total').each(function() {
                    const totalValue = parseInt($(this).text().replace(/\./g, '').replace('₫', '')
                        .trim());
                    if (!isNaN(totalValue)) {
                        taxableAmount += totalValue;
                    }
                });
                $('#taxable_amount_edit').text(formatPrice(taxableAmount));

                let discount = parseInt($('#discount_edit').val()); // Đảm bảo rằng discount là một số
                if (isNaN(discount)) discount = 0; // Nếu discount không phải là số, đặt mặc định là 0
                // Tính toán totalAmount với discount đã được xác nhận là số
                totalAmount = taxableAmount * (1 - discount / 100) * 1.1;
                $('#total_amount_input_edit').val(parseInt(totalAmount));
                $('#total_amount_edit').text(formatPrice(parseInt(totalAmount)));
            }

            $('#formEdit').submit(function(e) {
                e.preventDefault(); // Ngăn không cho form gửi theo cách thông thường

                var formData = new FormData(this);
                $('#productTableEdit tr').slice(0, -1).each(function(index) {
                    var productId = $(this).find('.delete-product-button').data('productId');
                    var quantity = $(this).find('.quantity').val();

                    if (productId && quantity !== undefined && quantity !== '') {
                        formData.append('products[' + index + '][productId]', productId);
                        formData.append('products[' + index + '][quantity]', quantity);
                    }
                });


                $.ajax({
                    url: `/invoice/update/${invoice_id}`,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#closeDrawerEdit').click();
                        $('#productTableEdit tr:not(:last)').remove();
                        reloadData('');
                        alert('Hóa đơn đã được cập nhật thành công!');
                        $('#formEdit').trigger('reset'); // Reset form
                    },
                    error: function(xhr) {
                        alert('Có lỗi xảy ra khi cập nhật hóa đơn.');
                    }
                });
            });
        });
    </script>

    {{-- delete --}}
    <script>
        $(document).ready(function() {
            // Sử dụng event delegation để gắn sự kiện click cho tất cả các nút hiện tại và tương lai
            $(document).on('click', '.deleteSupplierButton', function() {
                // Mở drawer
                const drawerId = $(this).data('drawer-target'); // Lấy ID của drawer từ thuộc tính data
                const drawerElement = $('#' + drawerId);
                const idInvoice = $(this).data('id-invoice');

                $('#contentDelete').html('Bạn có chắc chắn muốn xóa <strong> Hóa Đơn ' + idInvoice +
                    '</strong> không?');

                $(document).off('click', '#deleteBtn').on('click', '#deleteBtn', function() {

                    const url = '/invoice/delete/' + idInvoice;

                    $.ajax({
                        url: url, // Sử dụng nối chuỗi để thêm idSupplier vào URL
                        type: 'GET',
                        success: function(response) {
                            // Xử lý khi xóa thành công
                            alert(response.success);

                            $('#closeDrawerDelete').click();

                            $.ajax({
                                url: '/invoice/filter/' + "",
                                type: 'GET',
                                success: function(data) {
                                    $('#invoiceTable').html(
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

    <script>
        $(document).on('input', '.quantity', function() {
            var inventory = $(this).closest('tr').find('.inventory').text();
            if ($('#invoice_type_edit').val() == '0') {
                $(this).removeAttr('max');
            } else {
                if (inventory == 1) {
                    $(this).removeAttr('max');
                } else {
                    $(this).attr('max', inventory);
                }
            }
        });
    </script>

</x-app-layout>
