<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChildPaymentHistoryController extends Controller
{
    // 指定したユーザーの「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(User $child): View
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // 対象のお子様の支出データを取得
        $transactions = $child->transactions()
            ->where('type', 'expense')
            ->where('category', 'expense')
            // ->orderByDesc('transaction_date')
            ->paginate(10);

        return view('parent.child-payment-history.index', [
            'child' => $child,
            'currentBalance' => $this->balanceService->for($child),
            'transactions' => $transactions,
        ]);
    }
}
