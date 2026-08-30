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
        // お手伝い実績登録と収入の登録の処理を１つのtransactionとして実行
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
        // お手伝い実績と収入を１つのtransactionとして同時に更新
        DB::transaction(function () use ($choreRecord, $chore, $attributes): void {
            // 指定されたChoreRecordのデータを取得し、更新用のロックをかける
            $record = ChoreRecord::query()
                ->lockForUpdate()
                ->findOrFail($choreRecord->id);

            // お手伝い実績に紐づいているTransactionデータ探し、更新用ロックをかける
            $transaction = $record
                ->transaction()
                ->withTrashed()
                ->lockForUpdate()
                ->first();

            // お手伝い実績に紐づいているTransactionデータが存在しない時は処理を中断する
            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため編集できません。');
            }

            // お手伝い実績を更新
            $record->update($attributes);
            // お手伝い実績に紐づくデータを更新
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
        // transactionを使用してお手伝い実績とそれに紐づくデータを安全に削除
        DB::transaction(function () use ($choreRecord): void {
            // 指定されたChoreRecordのデータを取得し、更新用のロックをかける
            $record = ChoreRecord::query()
                ->lockForUpdate()
                ->findOrFail($choreRecord->id);

            // お手伝い実績に紐づいているTransactionデータ探し、更新用ロックをかける
            $transaction = $record
                ->transaction()
                ->withTrashed()
                ->lockForUpdate()
                ->first();

            // お手伝い実績に紐づいているTransactionデータが存在しない時は処理を中断する
            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため削除できません。');
            }

            // 削除処理
            if (! $transaction->trashed()) {
                $transaction->delete();
            }
            $record->delete();
        });
    }
}
