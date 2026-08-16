<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentSavingController extends Controller
{
    // 指定したユーザーの「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(User $child): View
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // お子様の貯金目標
        $savingGoal = $child->savingGoal;
        // お子様の現在残高を計算
        $currentBalance = $this->balanceService->for($child);

        $achievementRate = 0.0; // 達成率
        $remainingAmount = 0; // 残高

        // おこづかいの目標金額が設定されていたら
        if ($savingGoal !== null) {
            // 目標までの残金を計算 (目標金額 - 現在残高)
            $remainingAmount = max($savingGoal->target_amount - $currentBalance, 0);
            // 貯金目標に対する達成率を計算
            $achievementRate = $savingGoal->target_amount > 0
                ? max(($currentBalance / $savingGoal->target_amount) * 100, 0)
                : 0;
        }

        return view('parent.savings.show', [
            'child' => $child, // お子様のデータ
            'savingGoal' => $savingGoal, // 貯金目標
            'currentBalance' => $currentBalance, // 現在残高
            'remainingAmount' => $remainingAmount, // 目標までの残金
            'achievementRate' => $achievementRate, // 達成率の計算
            'progressRate' => min($achievementRate, 100), // 達成率の%表示
        ]);
    }
}
