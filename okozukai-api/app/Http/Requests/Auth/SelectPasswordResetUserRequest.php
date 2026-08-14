<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SelectPasswordResetUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['user_id' => ['required', 'integer']];
    }

    public function messages(): array
    {
        return ['user_id.required' => 'パスワードを変更するユーザーを選択してください。'];
    }
}
