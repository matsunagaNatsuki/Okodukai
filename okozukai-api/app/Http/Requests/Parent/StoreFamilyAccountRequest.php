<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// 家族アカウント新規作成FormRequest
class StoreFamilyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'parent' && $this->user()->family_id !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        // 保護者アカウント登録のルートか
        $isParent = $this->routeIs('parent.family-account.parents.store');

        return [
            'name' => ['required', 'string', 'max:100'],
            // 保護者のみメールアドレスを使用
            'email' => $isParent
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')]
                : ['nullable', Rule::prohibitedIf(filled($this->input('email')))],
            // 子どものみログインIDを使用
            'login_id' => $isParent
                ? ['nullable', Rule::prohibitedIf(filled($this->input('login_id')))]
                : [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('users', 'login_id')->where('family_id', $this->user()->family_id),
                ],
            'password' => ['required', 'string', 'min:8', 'max:15', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => '名前', 'email' => 'メールアドレス', 'login_id' => 'ログインID', 'password' => 'パスワード'];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'email.email' => 'メールアドレスを正しい形式で入力してください。',
            'email.unique' => 'このメールアドレスはすでに登録されています。',
            'login_id.unique' => 'このログインIDは同じ家族ですでに使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.max' => 'パスワードは15文字以下で入力してください。',
            'password.confirmed' => 'パスワード確認が一致していません。',
        ];
    }
}
