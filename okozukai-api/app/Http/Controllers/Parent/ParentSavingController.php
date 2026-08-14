<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentSavingController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        $savingGoal = $child->savingGoal;
        $currentBalance = $this->balanceService->for($child);

        $achievementRate = 0.0;
        $remainingAmount = 0;

        if ($savingGoal !== null) {
            $remainingAmount = max($savingGoal->target_amount - $currentBalance, 0);
            $achievementRate = $savingGoal->target_amount > 0
                ? max(($currentBalance / $savingGoal->target_amount) * 100, 0)
                : 0;
        }

        return view('parent.savings.show', [
            'child' => $child,
            'savingGoal' => $savingGoal,
            'currentBalance' => $currentBalance,
            'remainingAmount' => $remainingAmount,
            'achievementRate' => $achievementRate,
            'progressRate' => min($achievementRate, 100),
        ]);
    }
}
