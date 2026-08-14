<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildHomeController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(Request $request): View
    {
        $child = $request->user();
        $savingGoal = $child->savingGoal;
        $currentBalance = $this->balanceService->for($child);
        $recentTransactions = $child->transactions()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $remainingAmount = 0;
        $achievementRate = 0.0;

        if ($savingGoal !== null) {
            $remainingAmount = max($savingGoal->target_amount - $currentBalance, 0);
            $achievementRate = $savingGoal->target_amount > 0
                ? max(($currentBalance / $savingGoal->target_amount) * 100, 0)
                : 0;
        }

        return view('child.home', [
            'child' => $child,
            'savingGoal' => $savingGoal,
            'currentBalance' => $currentBalance,
            'recentTransactions' => $recentTransactions,
            'remainingAmount' => $remainingAmount,
            'achievementRate' => $achievementRate,
            'progressRate' => min($achievementRate, 100),
        ]);
    }
}
