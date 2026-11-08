<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

// お手伝いデータに紐付くテーブルを更新する処理を集約したクラス
class ChoreRecordService
{
    // お手伝い実績と収入に関するデータの新規作成
    public function create(User $child, Chore $chore, User $parent, array $attributes): ChoreRecord
    {
        return DB::transaction(function () use ($child, $chore, $parent, $attributes): ChoreRecord {
            // お手伝い実績登録
            $record = ChoreRecord::create([
                'user_id' => $child->id,
                'chore_id' => $chore->id,
                'registered_by' => $parent->id,
                'reward_amount' => $attributes['reward_amount'],
                // 'performed_at' => $attributes['performed_at'],
            ]);

            // お手伝いに対する収入の登録
            Transaction::create([
                'chore_record_id' => $record->id,
                'user_id' => $child->id,
                'type' => 'income',
                'category' => 'chore',
                'amount' => $attributes['reward_amount'],
                // 'transaction_date' => $attributes['performed_at'],
                'title' => $chore->chore_name,
                'created_by' => $parent->id,
            ]);

            return $record;
        });
    }

    // お手伝い実績と収入に関するデータの更新処理
    public function update(ChoreRecord $choreRecord, Chore $chore, array $attributes): void
    {
        DB::transaction(function () use ($choreRecord, $chore, $attributes): void {
            // お手伝い実績のデータを取得
            $record = ChoreRecord::query()
                ->lockForUpdate() // 他のデータの処理を止める
                ->findOrFail($choreRecord->id);

            // お手伝い実績に紐づいている収入履歴のデータを取得する
            $transaction = $record
                ->transaction() // リレーション取得
                ->withTrashed() // 論理削除したデータも含める
                ->lockForUpdate() // 他のデータの処理を止める
                ->first(); // データを一件のみ取得

            // お手伝い実績に紐づいた収入履歴がない時は処理を中断する
            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため編集できません。');
            }

            // お手伝い実績を更新
            $record->update($attributes);
            // お手伝い実績に紐づく収入履歴データを更新
            $transaction->update([
                'amount' => $attributes['reward_amount'],
                // 'transaction_date' => $attributes['performed_at'],
                'title' => $chore->chore_name,
            ]);
        });
    }

    // お手伝い実績と収入に関するデータの削除処理
    public function delete(ChoreRecord $choreRecord): void
    {
        DB::transaction(function () use ($choreRecord): void {
            // お手伝い実績のデータを取得
            $record = ChoreRecord::query()
                ->lockForUpdate() // 他のデータの処理を止める
                ->findOrFail($choreRecord->id); // 指定したデータのみ取得

            // お手伝い実績に紐づいた収入履歴のデータを取得
            $transaction = $record
                ->transaction() // 収入履歴のリレーション
                ->withTrashed() // 論理削除したデータも含める
                ->lockForUpdate() // 他のデータの処理を止める
                ->first(); // 一件のデータのみ取得

            // お手伝い実績に紐づいた収入履歴のデータを取得
            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため削除できません。');
            }

            // 収入履歴のデータが削除されていなかったら削除する
            if (! $transaction->trashed()) {
                $transaction->delete();
            }
            // お手伝い実績のデータを削除
            $record->delete();
        });
    }
}
