<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class ChoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'parent' && $this->user()->family_id !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'chore_name' => ['required', 'string', 'max:100'],
            'reward_amount' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'chore_name' => 'お手伝い名',
            'reward_amount' => '報酬金額',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'chore_name.max' => 'お手伝い名は100文字以内で入力してください。',
            'reward_amount.integer' => '報酬金額は整数で入力してください。',
            'reward_amount.min' => '報酬金額は1円以上で入力してください。',
        ];
    }
}
