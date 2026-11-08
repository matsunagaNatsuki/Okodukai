<?php

namespace App\Http\Requests\Parent;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreChorePerformanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ログイン中の保護者ユーザが指定した子どもの情報を閲覧してもいいか？
        return $this->user()?->role === 'parent'
            && $this->user()->family_id !== null
            && Gate::forUser($this->user())->allows('viewFamilyChild', $this->route('child'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'chore_id' => [
                'required',
                'integer',
                // お手伝い実績テーブルにログイン中のユーザのchores_idがあるか？
                Rule::exists('chores', 'id')->where(
                    fn (Builder $query) => $query
                        // ログイン中のfamily_idを取得
                        ->where('family_id', $this->user()->family_id)
                        // 論理削除したデータは入れない
                        ->whereNull('deleted_at'),
                ),
            ],
            'reward_amount' => ['required', 'integer', 'min:1'],
            // 'performed_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'chore_id' => 'お手伝い種類',
            'reward_amount' => '報酬金額',
            // 'performed_at' => '実施日',
        ];
    }

    /**
     * @return array<string, string>
     */
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
