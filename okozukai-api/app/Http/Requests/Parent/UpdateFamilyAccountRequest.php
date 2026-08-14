<?php

namespace App\Http\Requests\Parent;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateFamilyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('manageFamilyUser', $this->route('account'));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User $account */
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => $account->role === 'parent'
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($account->id)]
                : ['nullable', Rule::prohibitedIf(filled($this->input('email')))],
            'login_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'login_id')
                    ->where('family_id', $this->user()->family_id)
                    ->ignore($account->id),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)],
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
