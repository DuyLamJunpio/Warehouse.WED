<x-app-layout>
    {{-- Header & Breadcrumb --}}
    <div class="mb-6">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Trang chủ</a>
                </li>
                <li>
                    <span class="mx-1 text-slate-400">/</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">Hồ sơ cá nhân</span>
                </li>
            </ol>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            Hồ sơ & Tài khoản
        </h1>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Quản lý thông tin cá nhân, ảnh đại diện và bảo mật tài khoản quản trị.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Avatar & Password --}}
        <div class="space-y-6">
            {{-- Avatar Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 shadow-xs text-center">
                <div class="relative inline-block mx-auto mb-4 group cursor-pointer" onclick="$('#avatar').click()">
                    <img id="preview-image"
                        class="w-28 h-28 rounded-2xl object-cover border-2 border-indigo-100 dark:border-indigo-900/60 shadow-sm"
                        src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=128' }}"
                        alt="{{ $user->name }}">
                    <div class="absolute inset-0 bg-black/40 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $user->email }}</p>
                <div class="mt-2">
                    <x-badge :variant="$user->role == 1 ? 'purple' : 'info'" size="xs">
                        {{ $user->role == 1 ? 'Quản trị viên (Admin)' : 'Nhân viên' }}
                    </x-badge>
                </div>

                <form action="{{ route('profile.upload') }}" method="post" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <input type="file" name="avatar" hidden id="avatar" accept="image/*">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Cập nhật ảnh đại diện</span>
                    </button>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    @if (session('status') === 'profile-upload')
                        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">Đã cập nhật ảnh thành công!</p>
                    @endif
                </form>
            </div>

            {{-- Change Password Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Đổi mật khẩu</h3>
                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" required autocomplete="new-password"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                            class="block w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-600 transition-all">
                            <span>Lưu mật khẩu mới</span>
                        </button>
                        @if (session('status') === 'password-updated')
                            <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 text-center">Đã đổi mật khẩu thành công!</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: Profile Info Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Thông tin chi tiết</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Cập nhật họ tên, địa chỉ và thông tin liên hệ của bạn.</p>

                <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Họ</label>
                            <input type="text" id="lastname" name="lastname" value="{{ old('lastname', $lastName) }}" placeholder="Nguyễn"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <x-input-error class="mt-1" :messages="$errors->get('lastname')" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tên</label>
                            <input type="text" id="firstname" name="firstname" value="{{ old('firstname', $firstName) }}" placeholder="Văn A"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <x-input-error class="mt-1" :messages="$errors->get('firstname')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" readonly
                                class="block w-full text-sm rounded-xl border-slate-300 bg-slate-100 px-3.5 py-2.5 shadow-xs text-slate-500 cursor-not-allowed dark:bg-slate-700/50 dark:border-slate-600 dark:text-slate-400">
                            <p class="mt-1 text-[11px] text-slate-400">Email dùng làm tên đăng nhập cố định.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số điện thoại</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="0901234567"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <x-input-error class="mt-1" :messages="$errors->get('phone_number')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ</label>
                            <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Hà Nội / TP.HCM"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <x-input-error class="mt-1" :messages="$errors->get('address')" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ngày sinh</label>
                            <input type="date" name="birthday" value="{{ old('birthday', $user->birthday) }}"
                                class="block w-full text-sm rounded-xl border-slate-300 bg-white px-3.5 py-2.5 shadow-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <x-input-error class="mt-1" :messages="$errors->get('birthday')" />
                        </div>
                    </div>

                    <input type="hidden" id="name" name="name" value="{{ old('name', $user->name) }}">

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80 dark:border-slate-700/80">
                        @if (session('status') === 'profile-updated')
                            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Đã lưu thông tin!</p>
                        @endif
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition-all">
                            <span>Lưu thông tin hồ sơ</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#firstname, #lastname').on('input', function() {
                var firstName = $('#firstname').val().trim();
                var lastName = $('#lastname').val().trim();
                $('#name').val((lastName + ' ' + firstName).trim());
            });

            var imageInput = $("#avatar");
            var previewImage = $("#preview-image");

            imageInput.on("change", function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        previewImage.attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</x-app-layout>
