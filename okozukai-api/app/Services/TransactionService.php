<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    // お子様の「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    // 使用したおこづかいを記録する処理
    public function recordExpense(User $user, array $attributes): Transaction
    {
        // transactionを使用して現在残高の計算と収支履歴を記録
        return DB::transaction(function () use ($user, $attributes): Transaction {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

                // 使用金額が現在残高を超えるときはエラーを出す
            if ($attributes['amount'] > $this->balanceService->for($lockedUser)) {
                throw ValidationException::withMessages([
                    'amount' => '現在残高を超える金額は登録できません。',
                ]);
            }

            // テーブルに使用履歴データを保存
            return Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'expense',
                'category' => 'expense',
                'amount' => $attributes['amount'],
                // 'transaction_date' => $attributes['used_at'],
                'title' => $attributes['title'],
                'created_by' => $lockedUser->id,
            ]);
        });
    }
}
