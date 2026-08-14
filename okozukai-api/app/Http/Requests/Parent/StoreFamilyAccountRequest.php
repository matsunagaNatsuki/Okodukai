<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreFamilyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'parent' && $this->user()->family_id !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $isParent = $this->routeIs('parent.family-account.parents.store');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => $isParent
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')]
                : ['nullable', Rule::prohibitedIf(filled($this->input('email')))],
            'login_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'login_id')->where('family_id', $this->user()->family_id),
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
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
            'password.confirmed' => 'パスワード確認が一致していません。',
        ];
    }
}
