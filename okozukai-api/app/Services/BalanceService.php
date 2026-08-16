<?php

namespace App\Services;

use App\Models\User;

// お子様の「現在残高」を計算するためのService
class BalanceService
{
    public function for(User $user): int
    {
        // 対象のお子様に紐づいた収入、支出のリレーション
        $totals = $user->transactions()
            // transactionsテーブルのtypeカラムがincomeの際の収入データ
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income_total")
            // transactionsテーブルのtypeカラムがexpenseの際の支出データ
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense_total")
            ->first();

            // 収入データを支出データを計算して現在の残高数値の値を出す
        return (int) $totals->income_total - (int) $totals->expense_total;
    }
}
