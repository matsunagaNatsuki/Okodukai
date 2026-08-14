<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChildLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'family_code' => ['required', 'string', 'size:8', 'regex:/^\d{8}$/'],
            'login_id' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'save_login' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'family_code' => '家族コード',
            'login_id' => 'ログインID',
            'password' => 'パスワード',
            'save_login' => 'ログイン情報を保存する',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'family_code.size' => '家族コードは8桁で入力してください。',
            'family_code.regex' => '家族コードは8桁の数字で入力してください。',
            'login_id.max' => 'ログインIDは100文字以内で入力してください。',
        ];
    }
}
