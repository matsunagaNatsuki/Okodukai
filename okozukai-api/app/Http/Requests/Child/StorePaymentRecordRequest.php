<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'child';
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'used_at' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return ['title' => '使った内容', 'amount' => '金額', 'used_at' => '使用日'];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'title.max' => '使った内容は255文字以内で入力してください。',
            'amount.integer' => '金額は整数で入力してください。',
            'amount.min' => '金額は1円以上で入力してください。',
            'used_at.date' => '使用日を正しい日付で入力してください。',
        ];
    }
}
