<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\UpsertSavingGoalRequest;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildSavingController extends Controller
{
    // お子様の「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    // 貯金目標画面
    public function show(Request $request): View
    {
        // ログイン中のお子様ユーザー
        $child = $request->user();
        // お子様貯金目標
        $savingGoal = $child->savingGoal;
        // 現在残高を計算
        $currentBalance = $this->balanceService->for($child);

        // 目標までの残金を計算（目標がなかったら0)
        $remainingAmount = $savingGoal === null
            ? 0 : max($savingGoal->target_amount - $currentBalance, 0);
        // 貯金目標に対する達成率を計算
        $achievementRate = $savingGoal === null
            ? 0.0 : max(($currentBalance / $savingGoal->target_amount) * 100, 0);

        return view('child.savings.show', [
            'savingGoal' => $savingGoal, // 貯金目標
            'currentBalance' => $currentBalance, // 現在残高
            'remainingAmount' => $remainingAmount, // 目標までの残金
            'achievementRate' => $achievementRate, // 達成率の計算
            'progressRate' => min($achievementRate, 100), // 達成率の%表示
        ]);
    }

    // 貯金目標設定処理
    public function store(UpsertSavingGoalRequest $request): RedirectResponse
    {
        $child = $request->user();
        $validated = $request->validated();
        $currentBalance = $this->balanceService->for($child);

        $child->savingGoal()->updateOrCreate(
            ['user_id' => $child->id],
            [
                'item_name' => $validated['item_name'],
                'target_amount' => $validated['target_amount'],
                'is_completed' => $currentBalance >= $validated['target_amount'],
            ],
        );

        return redirect()
            ->route('child.savings.show')
            ->with('success', '貯金目標を保存しました。');
    }
}
