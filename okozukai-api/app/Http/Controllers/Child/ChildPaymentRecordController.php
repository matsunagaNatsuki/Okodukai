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
        // お子様の「現在残高」を計算するためのService
        private readonly BalanceService $balanceService,
        // 使用したおこづかいを記録処理を行うService
        private readonly TransactionService $transactionService,
    ) {}

    // おこづかい使用記録画面
    public function create(Request $request): View
    {
        return view('child.payment-record.create', [
            // お子様の「現在残高」を計算する
            'currentBalance' => $this->balanceService->for($request->user()),
        ]);
    }

    // 使用したおこづかいを記録する処理
    public function store(StorePaymentRecordRequest $request): RedirectResponse
    {
        // TransactionServiceでおこづかいの登録処理を行う
        $this->transactionService->recordExpense($request->user(), $request->validated());

        return redirect()
            ->route('child.home')
            ->with('success', 'つかったものを記録しました。');
    }
}
