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
    public function __construct(private readonly FamilyAccountService $accountService) {}

    public function index(Request $request): View
    {
        $family = $request->user()->family;
        $accounts = $family->users()
            ->orderByRaw("CASE WHEN role = 'parent' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('parent.family-account.index', compact('family', 'accounts'));
    }

    public function storeParent(StoreFamilyAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated(), 'parent');

        return $this->storedResponse('保護者を追加しました。');
    }

    public function storeChild(StoreFamilyAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated(), 'child');

        return $this->storedResponse('子どもを追加しました。');
    }

    public function update(UpdateFamilyAccountRequest $request, User $account): RedirectResponse
    {
        Gate::authorize('manageFamilyUser', $account);
        $this->accountService->update($account, $request->validated());

        return redirect()
            ->route('parent.family-account.index')
            ->with('success', '家族アカウントを更新しました。');
    }

    public function destroy(Request $request, User $account): JsonResponse
    {
        Gate::authorize('deleteFamilyUser', $account);

        $account->delete();

        return response()->json(['message' => '家族アカウントを削除しました。']);
    }

    private function storedResponse(string $message): RedirectResponse
    {
        return redirect()
            ->route('parent.family-account.index')
            ->with('success', $message);
    }
}
