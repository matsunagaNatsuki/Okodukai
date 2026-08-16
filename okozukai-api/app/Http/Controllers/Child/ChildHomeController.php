<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildHomeController extends Controller
{
    // お子様の「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(Request $request): View
    {
        // ログイン中のお子様ユーザを取得
        $child = $request->user();
        // お子様の貯金目標
        $savingGoal = $child->savingGoal;
        // お子様の現在残高を計算
        $currentBalance = $this->balanceService->for($child);
        // 収支の履歴
        $recentTransactions = $child->transactions()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $remainingAmount = 0; // 現在残高
        $achievementRate = 0.0; //達成率

        // おこづかいの目標金額が設定されていたら
        if ($savingGoal !== null) {
            // 目標までの残金を計算 (目標金額 - 現在残高)
            $remainingAmount = max($savingGoal->target_amount - $currentBalance, 0);
            // 貯金目標に対する達成率を計算
            $achievementRate = $savingGoal->target_amount > 0
                ? max(($currentBalance / $savingGoal->target_amount) * 100, 0)
                : 0;
        }

        return view('child.home', [
            'child' => $child, // お子様のデータ
            'savingGoal' => $savingGoal, // 貯金目標
            'currentBalance' => $currentBalance,  // 現在残高
            'recentTransactions' => $recentTransactions, // 収支の履歴
            'remainingAmount' => $remainingAmount, // 目標までの残金
            'achievementRate' => $achievementRate, // 達成率の計算
            'progressRate' => min($achievementRate, 100), // 達成率の%表示
        ]);
    }
}
