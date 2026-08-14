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
    public function __construct(private readonly BalanceService $balanceService) {}

    public function show(Request $request): View
    {
        $child = $request->user();
        $savingGoal = $child->savingGoal;
        $currentBalance = $this->balanceService->for($child);
        $remainingAmount = $savingGoal === null
            ? 0
            : max($savingGoal->target_amount - $currentBalance, 0);
        $achievementRate = $savingGoal === null
            ? 0.0
            : max(($currentBalance / $savingGoal->target_amount) * 100, 0);

        return view('child.savings.show', [
            'savingGoal' => $savingGoal,
            'currentBalance' => $currentBalance,
            'remainingAmount' => $remainingAmount,
            'achievementRate' => $achievementRate,
            'progressRate' => min($achievementRate, 100),
        ]);
    }

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
