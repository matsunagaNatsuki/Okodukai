<?php

namespace App\Http\Requests\Parent;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateChoreRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'parent'
            && Gate::forUser($this->user())->allows('viewFamilyChild', $this->route('child'))
            && Gate::forUser($this->user())->allows('manageChoreRecord', $this->route('choreRecord'));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'chore_id' => [
                'required',
                'integer',
                Rule::exists('chores', 'id')->where(
                    fn (Builder $query) => $query
                        ->where('family_id', $this->user()->family_id)
                        ->whereNull('deleted_at'),
                ),
            ],
            'reward_amount' => ['required', 'integer', 'min:1'],
            // 'performed_at' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'chore_id' => 'お手伝い内容',
            'reward_amount' => '報酬金額',
            // 'performed_at' => '実施日',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'chore_id.exists' => '選択したお手伝い設定を確認してください。',
            'reward_amount.integer' => '報酬金額は整数で入力してください。',
            'reward_amount.min' => '報酬金額は1円以上で入力してください。',
            // 'performed_at.date' => '実施日を正しい日付で入力してください。',
        ];
    }
}
