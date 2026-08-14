<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ChoreRecordService
{
    public function create(User $child, Chore $chore, User $parent, array $attributes): ChoreRecord
    {
        return DB::transaction(function () use ($child, $chore, $parent, $attributes): ChoreRecord {
            $record = ChoreRecord::create([
                'user_id' => $child->id,
                'chore_id' => $chore->id,
                'registered_by' => $parent->id,
                'reward_amount' => $attributes['reward_amount'],
                'performed_at' => $attributes['performed_at'],
            ]);

            Transaction::create([
                'chore_record_id' => $record->id,
                'user_id' => $child->id,
                'type' => 'income',
                'category' => 'chore',
                'amount' => $attributes['reward_amount'],
                'transaction_date' => $attributes['performed_at'],
                'title' => $chore->chore_name,
                'created_by' => $parent->id,
            ]);

            return $record;
        });
    }

    public function update(ChoreRecord $choreRecord, Chore $chore, array $attributes): void
    {
        DB::transaction(function () use ($choreRecord, $chore, $attributes): void {
            $record = ChoreRecord::query()->lockForUpdate()->findOrFail($choreRecord->id);
            $transaction = $record->transaction()->withTrashed()->lockForUpdate()->first();

            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため編集できません。');
            }

            $record->update($attributes);
            $transaction->update([
                'amount' => $attributes['reward_amount'],
                'transaction_date' => $attributes['performed_at'],
                'title' => $chore->chore_name,
            ]);
        });
    }

    public function delete(ChoreRecord $choreRecord): void
    {
        DB::transaction(function () use ($choreRecord): void {
            $record = ChoreRecord::query()->lockForUpdate()->findOrFail($choreRecord->id);
            $transaction = $record->transaction()->withTrashed()->lockForUpdate()->first();

            if ($transaction === null) {
                throw new ConflictHttpException('対応する収入取引を特定できないため削除できません。');
            }

            if (! $transaction->trashed()) {
                $transaction->delete();
            }
            $record->delete();
        });
    }
}
