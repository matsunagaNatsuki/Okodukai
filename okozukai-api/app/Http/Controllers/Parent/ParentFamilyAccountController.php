<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\StoreFamilyAccountRequest;
use App\Http\Requests\Parent\UpdateFamilyAccountRequest;
use App\Models\User;
use App\Services\FamilyAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentFamilyAccountController extends Controller
{
    // 家族アカウントの新規作成を編集を行うService
    public function __construct(private readonly FamilyAccountService $accountService) {}

    // 家族アカウント画面の表示
    public function index(Request $request): View
    {
        // ログイン中のユーザーが所属する家族情報を取得
        $family = $request->user()->family;

        // 家族に所属するアカウントを保護者、子どもの順に取得
        $accounts = $family->users()
            ->orderByRaw("CASE WHEN role = 'parent' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('parent.family-account.index', compact('family', 'accounts'));
    }

    // 保護者アカウントの追加処理
    public function storeParent(StoreFamilyAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated(), 'parent');

        return redirect()
            ->route('parent.family-account.index')
            ->with('success', '保護者を追加しました。');
        // return $this->storedResponse('保護者を追加しました。');
    }

    // お子様アカウントの追加処理
    public function storeChild(StoreFamilyAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated(), 'child');

        return redirect()
            ->route('parent.family-account.index')
            ->with('success', 'お子様を追加しました。');
        // return $this->storedResponse('お子様を追加しました。');
    }

    // 家族アカウントの編集処理
    public function update(UpdateFamilyAccountRequest $request, User $account): RedirectResponse
    {
        // ログイン中の保護者がこの家族アカウントを操作してもよいか
        Gate::authorize('manageFamilyUser', $account);
        $this->accountService->update($account, $request->validated());

        return redirect()
            ->route('parent.family-account.index')
            ->with('success', '家族アカウントを更新しました。');
    }

    // 家族アカウントの削除処理
    public function destroy(User $account): JsonResponse
    {
        // 同じ家族のアカウントかつ、本人・家族オーナー以外の場合のみ削除を許可
        Gate::authorize('deleteFamilyUser', $account);

        $account->delete();

        return response()->json(['message' => '家族アカウントを削除しました。']);
    }

    // アカウントの追加処理後のメッセージ表示
    // private function storedResponse(string $message): RedirectResponse
    // {
    //     return redirect()
    //         ->route('parent.family-account.index')
    //         ->with('success', $message);
    // }
}
