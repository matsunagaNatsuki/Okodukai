<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['password' => ['required', 'confirmed', Password::min(8)]];
    }

    public function messages(): array
    {
        return [
            'password.required' => '新しいパスワードを入力してください。',
            'password.confirmed' => '新しいパスワード確認が一致しません。',
            'password.min' => '新しいパスワードは8文字以上で入力してください。',
        ];
    }
}
