<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*[0-9]).+$/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'      => 'Vui lòng nhập đầy đủ thông tin.',
            'password.required'              => 'Vui lòng nhập đầy đủ thông tin.',
            'password.min'                   => 'Mật khẩu phải bao gồm ít nhất 8 ký tự, 1 chữ hoa, 1 chữ số.',
            'password.regex'                 => 'Mật khẩu phải bao gồm ít nhất 8 ký tự, 1 chữ hoa, 1 chữ số.',
            'password_confirmation.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'password_confirmation.same'     => 'Mật khẩu nhập lại không trùng khớp.',
        ];
    }
}