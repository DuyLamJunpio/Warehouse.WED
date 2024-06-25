<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequestAPI;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email', //Phải có dạng email
            'password' => 'required',
            'device_imei' => 'required', // mã số imei của thiết bị
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($request->device_imei)->plainTextToken,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'], // Giới hạn tên tối đa 255 ký tự
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class], // Tất cả ký tự in thường, có dạng email, Giới hạn tối đa 255 ký tự, email phải là duy nhất
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($user->id) {
            return response()->json([
                'message' => 'Đăng ký thành công!',
                'user' => $user,
                // Các thông tin khác bạn muốn bao gồm trong phản hồi
            ], 201); // 201 là mã trạng thái "Created"
        } else {
            return response()->json([
                'message' => 'Đăng ký không thành công!',
            ], 422); // 422 là mã trạng thái "Unprocessable Entity"
        }
    }

    public function staff()
    {
        $perPage = 15;
        $staff = User::where('role', 0)->paginate($perPage);
        return response()->json($staff);
    }

    public function admin(string $id)
    {
        $admin = User::find($id);
        return $admin;
    }
    public function getStaff(string $id)
    {
        $staff = User::find($id);
        return $staff;
    }

    public function update(ProfileUpdateRequestAPI $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
        }

        $user->fill($request->validated());

        $result = $user->save();
        if ($result) {
            return response()->json(['success' => 'Cập nhật thành công!']);
        } else {
            return response()->json(['error' => 'Cập nhật thất bại.'], 500);
        }
    }

    public function password_update(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            //password_confirmation
        ]);

        $result = $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($result) {
            return response()->json(['success' => 'Cập nhật thành công!']);
        } else {
            return response()->json(['error' => 'Cập nhật thất bại.'], 500);
        }

    }

    public function upload(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
        }

        $request->validate([
            'avatar' => 'required|file|image|max:2048', //Bắt buộc, phải là file, phải là ảnh, Giới hạn 2MB
        ]);

        if ($request->hasFile('avatar')) {
            // Kiểm tra và xóa ảnh cũ nếu tồn tại
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }

            //Lưu ảnh mới và cập nhật đường dẫn
            $avatar = $request->file('avatar');
            $user->avatar = $avatar->store('public/images');
            $result = $user->save();
            if ($result) {
                return response()->json(['success' => 'Cập nhật thành công!']);
            } else {
                return response()->json(['error' => 'Cập nhật thất bại.'], 500);
            }
        }
    }

    public function destroy(Request $request, string $id, User $user)
    {
        $this->authorize('delete', $user);
        // Xác thực mật khẩu của admin đang đăng nhập
        $request->validate([
            'password' => ['required'],
        ]);

        // Kiểm tra mật khẩu
        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['error' => 'Mật khẩu không chính xác.'], 401);
        }

        // Tìm và xóa tài khoản nhân viên
        $staff = User::find($id);
        if (!$staff) {
            return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
        }

        if ($staff->delete()) {
            // Không cần logout và làm mới session vì đây là API
            return response()->json(['success' => 'Xóa tài khoản thành công!']);
        } else {
            return response()->json(['error' => 'Xóa tài khoản thất bại.']);
        }
    }
}
