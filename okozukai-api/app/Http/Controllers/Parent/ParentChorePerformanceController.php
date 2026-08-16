<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\StoreChorePerformanceRequest;
use App\Models\Chore;
use App\Models\User;
use App\Services\ChoreRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentChorePerformanceController extends Controller
{
    // お手伝いデータに紐付くテーブルを更新する処理を集約したクラス
    public function __construct(private readonly ChoreRecordService $choreRecordService){}

    // お手伝い実績登録
    public function create(User $child): View
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // 家族idに紐づいたお手伝い報酬設定
        $chores = Chore::query()
            ->where('family_id', request()->user()->family_id)
            ->orderBy('chore_name')
            ->get();

        return view('parent.chores-performance.create', [
            'child' => $child,
            'chores' => $chores,
        ]);
    }

    // お手伝い実績登録処理
    public function store(StoreChorePerformanceRequest $request, User $child): RedirectResponse
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // バリデーションを通過したデータを取得
        $validated = $request->validated();

        // 家族idに紐づいたお手伝い報酬設定
        $chore = Chore::query()
            ->where('family_id', $request->user()->family_id)
            ->findOrFail($validated['chore_id']);

        // ChoreRecordServiceクラスでお手伝いの新規登録処理を行う
        $this->choreRecordService->create($child, $chore, $request->user(), $validated);

        return redirect()
            ->route('parent.chores.performance', $child)
            ->with('success', 'お手伝い実績を登録しました。');
    }
}
