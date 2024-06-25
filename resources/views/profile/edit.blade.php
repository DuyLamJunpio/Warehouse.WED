<x-app-layout>
    <div class="grid grid-cols-1 px-4 pt-6 xl:grid-cols-3 xl:gap-4 dark:bg-gray-900">
        <div class="mb-4 col-span-full xl:mb-2">
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
                            <a href="{{ route('profile.edit') }}"
                                class="ml-1 text-gray-700 hover:text-primary-600 md:ml-2 dark:text-gray-300 dark:hover:text-white">Profile</a>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Admin profile</h1>
        </div>
        <!-- Right Content -->
        <div class="col-span-full xl:col-auto">
            <div
                class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div
                    class="flex flex-col justify-center items-center sm:flex-row xl:flex-col 2xl:flex-row sm:space-x-4 xl:space-x-0 2xl:space-x-4">
                    <img class="mb-4 rounded-lg w-28 h-28 sm:mb-0 xl:mb-4 2xl:mb-0" id="preview-image"
                        src="{{ $user->avatar == null ? 'https://www.androidponsel.com/wp-content/uploads/2023/04/profil-kosong-bulat-dua.jpg' : Storage::url($user->avatar) }}"
                        alt=" picture">
                    <div>
                        <h3 class="text-center mb-1 text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}
                        </h3>
                        <div class="mb-4 text-sm text-gray-500 dark:text-gray-400"></div>
                        <div class="flex items-center space-x-4 justify-center w-full">
                            <form action="{{ route('profile.upload') }}" method="post" enctype="multipart/form-data"
                                class="w-full">
                                @csrf
                                <input type="file" name="avatar" hidden id="avatar">
                                <div class="text-center"> <!-- Bọc nút và thẻ p trong một div với text-center -->
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                                        <svg class="w-4 h-4 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z">
                                            </path>
                                            <path d="M9 13h2v5a1 1 0 11-2 0v-5z"></path>
                                        </svg>
                                        Upload picture
                                    </button>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                                @if (session('status') === 'profile-upload')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="mt-2 text-sm text-green-500 dark:text-green-400 text-center mx-auto">
                                        {{ __('Successfully!') }}
                                    </p>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flow-root">
                    <h3 class="mb-4 text-xl font-semibold dark:text-white">Password information</h3>
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-6">
                                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                                <x-text-input id="update_password_current_password" name="current_password"
                                    type="password" class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>
                            <div class="col-span-6 sm:col-span-6">
                                <x-input-label for="update_password_password" :value="__('New Password')" />
                                <x-text-input id="update_password_password" name="password" type="password"
                                    class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>
                            <div class="col-span-6 sm:col-span-6">
                                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="update_password_password_confirmation" name="password_confirmation"
                                    type="password" class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <x-flowbie-button>{{ __('Save Password') }}</x-flowbie-button>
                                @if (session('status') === 'password-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-500 dark:text-green-400">{{ __('Successfully!') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <div
                class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-semibold dark:text-white">Admin information</h3>
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="firstname" :value="__('Firstname')" />
                            <x-text-input id="firstname" name="firstname" type="text" :value="old('firstname', $firstName)"
                                placeholder="Type your firstname" autofocus autocomplete="firstname" />
                            <x-input-error class="mt-2" :messages="$errors->get('firstname')" />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="lastname" :value="__('Lastname')" />
                            <x-text-input id="lastname" name="lastname" type="text" :value="old('lastname', $lastName)"
                                placeholder="Type your lastname" autofocus autocomplete="lastname" />
                            <x-input-error class="mt-2" :messages="$errors->get('lastname')" />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" readonly
                                autofocus autocomplete="email" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" :value="old('address', $user->address)"
                                placeholder="Type your address" autofocus autocomplete="address" />
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" :value="old('phone_number', $user->phone_number)"
                                placeholder="Type your phone number" autofocus autocomplete="phone_number" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-input-label for="birthday" :value="__('Birthday')" />
                            <x-text-input datepicker datepicker-autohide datepicker-format="dd-mm-yyyy" id="birthday"
                                name="birthday" type="text" :value="old('birthday', $user->birthday)" placeholder="Select date" autofocus
                                autocomplete="birthday" />
                            <x-input-error class="mt-2" :messages="$errors->get('birthday')" />
                        </div>
                        <x-text-input id="name" name="name" style="width: 200px" type="hidden"
                            :value="old('name', $user->name)" autofocus autocomplete="name" />
                        <div class="col-span-6 sm:col-full">
                            <div class="flex items-center gap-4">
                                <x-flowbie-button>{{ __('Save Infomation') }}</x-flowbie-button>
                                @if (session('status') === 'profile-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-500 dark:text-green-400">{{ __('Successfully!') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div
                class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flow-root">
                    <h3 class="text-xl font-semibold dark:text-white">Sessions</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 dark:text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-semibold text-gray-900 truncate dark:text-white">
                                        California 123.123.123.123
                                    </p>
                                    <p class="text-sm font-normal text-gray-500 truncate dark:text-gray-400">
                                        Chrome on macOS
                                    </p>
                                </div>
                                <div class="inline-flex items-center">
                                    <a href="#"
                                        class="px-3 py-2 mb-3 mr-3 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Revoke</a>
                                </div>
                            </div>
                        </li>
                        <li class="pt-4 pb-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 dark:text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-semibold text-gray-900 truncate dark:text-white">
                                        Rome 24.456.355.98
                                    </p>
                                    <p class="text-sm font-normal text-gray-500 truncate dark:text-gray-400">
                                        Safari on iPhone
                                    </p>
                                </div>
                                <div class="inline-flex items-center">
                                    <a href="#"
                                        class="px-3 py-2 mb-3 mr-3 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Revoke</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div>
                        <button
                            class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">See
                            more</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Lắng nghe sự kiện input trên cả hai trường first-name và last-name
            $('#firstname, #lastname').on('input', function() {
                // Lấy giá trị từ mỗi trường
                var firstName = $('#firstname').val();
                var lastName = $('#lastname').val();

                // Cập nhật giá trị của trường name với firstName và lastName
                // Bạn có thể thêm một khoảng trắng giữa firstName và lastName nếu muốn
                $('#name').val(firstName + ' ' + lastName);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Lấy các phần tử DOM cần thiết
            var imageInput = $("#avatar");
            var previewImage = $("#preview-image");

            // Sự kiện click cho button "Choose image"
            previewImage.on("click", function() {
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

</x-app-layout>
