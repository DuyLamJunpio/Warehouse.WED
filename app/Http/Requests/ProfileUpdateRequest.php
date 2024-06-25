<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable','numeric', 'digits_between:9,11', Rule::unique(User::class)->ignore($this->user()->id)],
            'birthday' => ['nullable','date_format:d-m-Y','before:today'],
            'address' => ['nullable','max:255'],
        ];

    }
}
