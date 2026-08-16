<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildPaymentHistoryController extends Controller
{
    // お子様の「現在残高」を計算するためのService
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(Request $request): View
    {
        $child = $request->user();

        // 支出の履歴のデータをDBから取得
        $transactions = $child->transactions()
            ->where('type', 'expense')
            ->where('category', 'expense')
            // ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('child.payment-history.index', [
            'currentBalance' => $this->balanceService->for($child),
            'transactions' => $transactions,
        ]);
    }
}
