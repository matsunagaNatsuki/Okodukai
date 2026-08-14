<?php

namespace App\Services;

use App\Models\User;

class BalanceService
{
    public function for(User $user): int
    {
        $totals = $user->transactions()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense_total")
            ->first();

        return (int) $totals->income_total - (int) $totals->expense_total;
    }
}
