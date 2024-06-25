<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $userName = Auth::user()->name;

        $nameParts = explode(' ', $userName);

        if (count($nameParts) >= 2) {
            $firstName = $nameParts[0];
            $lastName = implode(' ', array_slice($nameParts, 1));
        } else {
            $firstName = $userName;
            $lastName = '';
        }
        return view('profile.edit', [
            'user' => $request->user(),
            'firstName' => $firstName,
            'lastName' => $lastName
        ]);

    }

    /**
     * Update the user's profile information.
     */

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|file|image|max:2048', // Giới hạn 2MB
        ]);

        if ($request->hasFile('avatar')) {
            // Kiểm tra và xóa ảnh cũ nếu tồn tại
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }

            //Lưu ảnh mới và cập nhật đường dẫn
            $avatar = $request->file('avatar');
            $user->avatar = $avatar->store('public/images');
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-upload');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
