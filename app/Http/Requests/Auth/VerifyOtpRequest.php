<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'otp.required'   => 'Vui lòng nhập đầy đủ thông tin.',
            'otp.size'       => 'Mã OTP phải gồm đúng 6 ký tự.',
        ];
    }
}