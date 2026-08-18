<?php

namespace App\Http\Requests\Parent;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// 家族アカウント編集用FormRequest
class UpdateFamilyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('manageFamilyUser', $this->route('account'));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        // Userアカウント id
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:100'],
            // 保護者ユーザの場合はemailを使用
            'email' => $account->role === 'parent'
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($account->id)]
                : ['nullable', Rule::prohibitedIf(filled($this->input('email')))],
            // 保護者ユーザの場合はログインIDを使用
            'login_id' => $account->role === 'parent'
                ? ['nullable', Rule::prohibitedIf(filled($this->input('login_id')))]
                : [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('users', 'login_id')
                        ->where('family_id', $this->user()->family_id)
                        ->ignore($account->id),
                ],
            'password' => ['nullable', 'string', 'min:8', 'max:15', 'confirmed'],
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
