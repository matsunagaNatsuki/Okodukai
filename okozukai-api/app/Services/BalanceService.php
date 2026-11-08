<?php

namespace App\Services;

use App\Models\User;

// お子様の「現在残高」を計算するためのService
class BalanceService
{
    public function for(User $user): int
    {
        // 対象のお子様に紐づいた収支のリレーション
        $totals = $user->transactions()
            // SQLでログイン中の収入のデータの合計を計算する
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income_total")
            // SQLでログイン中の支出のデータの合計を計算する
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense_total")
            ->first();

            // 合計した収入データと支出データを計算して現在残高の数値を出す
        return (int) $totals->income_total - (int) $totals->expense_total;
    }
}
