<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChildPaymentHistoryController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function __invoke(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        $transactions = $child->transactions()
            ->where('type', 'expense')
            ->where('category', 'expense')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('parent.child-payment-history.index', [
            'child' => $child,
            'currentBalance' => $this->balanceService->for($child),
            'transactions' => $transactions,
        ]);
    }
}
