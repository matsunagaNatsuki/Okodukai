<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPasswordResetCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['code' => ['required', 'digits:4']];
    }

    public function messages(): array
    {
        return ['code.required' => '確認コードを入力してください。', 'code.digits' => '確認コードは4桁の数字で入力してください。'];
    }
}
