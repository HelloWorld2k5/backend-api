<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        // Quy tắc cho POST (tạo tài khoản)
        if ($this->isMethod('post')) {
            $rules = [
                'username' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'role' => 'required|in:student,lecturer,faculty_staff,admin,company',
            ];
        }

        // Quy tắc cho PUT (sửa tài khoản)
        if ($this->isMethod('put')) {
            $rules = [
                'username' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'status' => 'sometimes|in:active,inactive',
                'reset_password' => 'sometimes|boolean',
            ];
        }

        return $rules;
    }
}
