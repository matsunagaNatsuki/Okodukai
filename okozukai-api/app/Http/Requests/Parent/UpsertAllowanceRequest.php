<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAllowanceRequest extends FormRequest
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
            'amount' => ['required', 'integer', 'min:1'],
            'payment_day' => ['required', 'integer', 'between:1,31'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount' => '毎月のおこづかい金額',
            'payment_day' => '支給日',
            'is_active' => '有効状態',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'integer' => ':attributeは整数で入力してください。',
            'amount.min' => '毎月のおこづかい金額は1円以上で入力してください。',
            'payment_day.between' => '支給日は1日から31日の間で選択してください。',
            'is_active.boolean' => '有効状態を正しく選択してください。',
        ];
    }
}
