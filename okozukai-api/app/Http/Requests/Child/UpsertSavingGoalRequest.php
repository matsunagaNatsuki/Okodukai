<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class UpsertSavingGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'child';
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'item_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'item_name' => '欲しいもの',
            'target_amount' => '目標金額',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'item_name.max' => '欲しいものは255文字以内で入力してください。',
            'target_amount.integer' => '目標金額は整数で入力してください。',
            'target_amount.min' => '目標金額は1円以上で入力してください。',
        ];
    }
}
