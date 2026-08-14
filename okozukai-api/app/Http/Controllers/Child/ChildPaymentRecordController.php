<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\StorePaymentRecordRequest;
use App\Services\BalanceService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildPaymentRecordController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly TransactionService $transactionService,
    ) {}

    public function create(Request $request): View
    {
        return view('child.payment-record.create', [
            'currentBalance' => $this->balanceService->for($request->user()),
        ]);
    }

    public function store(StorePaymentRecordRequest $request): RedirectResponse
    {
        $this->transactionService->recordExpense($request->user(), $request->validated());

        return redirect()
            ->route('child.home')
            ->with('success', 'つかったものを記録しました。');
    }
}
