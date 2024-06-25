<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequestAPI extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Bắt buộc, chữ cái, tối đa 255 ký tự
            'phone_number' => ['required','numeric', 'digits_between:9,11', Rule::unique(User::class)->ignore($this->user()->id)],
            // Bắt buộc, Chữ số, Cho phép số điện thoại từ 9 đến 11 chữ số, phải là duy nhất
            'birthday' => ['nullable','date_format:d-m-Y','before:today'],
            //Không bắt buộc, định dạng dd-mm-YYYY, ngày sinh phải là quá khứ
            'address' => ['nullable','max:255'],
             //Không bắt buộc, tối đa 255 ký tự
        ];

    }
}
