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
                                <span class="ml-1 text-gray-400 md:ml-2 dark:text-gray-500" aria-current="page">Product
                                    Location</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Product Location</h1>
            </div>
            <div class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">

                        <form class="sm:pr-3">
                            <select id="zones"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                @foreach ($zones as $item)
                                    <option value="{{ $item->zone }}">Kho {{ $item->zone }}</option>
                                @endforeach
                            </select>
                        </form>

                    </div>

                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">

                        <form class="sm:pr-3">
                            <select id="shelves"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </select>
                        </form>

                    </div>
                </div>
                <button id="createLocation"
                    class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800"
                    type="button" data-drawer-target="drawer-create-product-default"
                    data-drawer-show="drawer-create-product-default" aria-controls="drawer-create-product-default"
                    data-drawer-placement="right">
                    Add new location
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
                                    Code
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Kho
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Giá
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Tầng
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Sản phẩm
                                </th>
                            </tr>
                        </thead>
                        {{-- itemmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm --}}
                        <tbody id="locationTable"
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @include('location.data')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            filterZone(1);
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

            function filterZone(val) {
                $.ajax({
                    url: '{{ route('location') }}', // Đảm bảo bạn đã định nghĩa route này trong routes/web.php
                    type: 'GET',
                    data: {
                        zone: val
                    },
                    success: function(data) {
                        $('#shelves')
                            .empty(); // Giả sử bạn muốn cập nhật một element với id `someElement`
                        data.shelves.forEach(function(shelves) {
                            $('#shelves').append($('<option>', {
                                value: shelves.shelf,
                                text: 'Giá ' + shelves.shelf
                            }));
                        });
                    }
                });
            }


            function filterData(zone, shelf) {
                $.ajax({
                    url: '{{ route('location.getData') }}',
                    type: 'GET',
                    data: {
                        zone: zone,
                        shelf: shelf
                    },
                    success: function(data) {
                        console.log(data);
                        $('#locationTable').empty();
                        $('#locationTable').html(data);
                    }
                });
            }


            $('#zones').on('change', function() {
                var val = $(this).val();
                filterZone(val);
                filterData(val);
            });

            $('#shelves').on('change', function() {
                var val = $(this).val();
                filterData($('#zones').val(), val);
            });
        });
    </script>

</x-app-layout>
