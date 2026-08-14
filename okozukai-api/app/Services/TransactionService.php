<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function recordExpense(User $user, array $attributes): Transaction
    {
        return DB::transaction(function () use ($user, $attributes): Transaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($attributes['amount'] > $this->balanceService->for($lockedUser)) {
                throw ValidationException::withMessages([
                    'amount' => '現在残高を超える金額は登録できません。',
                ]);
            }

            return Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'expense',
                'category' => 'expense',
                'amount' => $attributes['amount'],
                'transaction_date' => $attributes['used_at'],
                'title' => $attributes['title'],
                'created_by' => $lockedUser->id,
            ]);
        });
    }
}
