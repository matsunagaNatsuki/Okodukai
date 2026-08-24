<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\UpdateChoreRecordRequest;
use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\User;
use App\Services\ChoreRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentChoreHistoryController extends Controller
{
    // お手伝いデータに紐付くテーブルを更新する処理を集約したクラス
    public function __construct(private readonly ChoreRecordService $choreRecordService) {}

    // お手伝い履歴一覧
    public function index(User $child): View
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // お子様のお手伝い履歴を登録
        $records = $child->choreRecords()
            ->with([
                'chore' => fn ($query) => $query->withTrashed(),
                'transaction',
            ])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->paginate(10);

        // $records = ChoreRecord::
        //     with(['chore', 'transaction'])
        //     ->orderByDesc('performed_at')
        //     ->orderByDesc('id')
        //     ->withTrashed()
        //     ->paginate(10);

        // 家族idに紐づいたお手伝い報酬設定
        $chores = Chore::query()
            ->where('family_id', request()->user()->family_id)
            ->orderBy('chore_name')
            ->get();

        return view('parent.chores-history.index', compact('child', 'records', 'chores'));
    }

    // お手伝い実績の編集
    public function update(
        UpdateChoreRecordRequest $request,
        User $child,
        ChoreRecord $choreRecord,
    ): RedirectResponse {
        // $this->ensureRecordBelongsToChild($child, $choreRecord);

        // バリデーションを通過したデータを取得
        $validated = $request->validated();

        // 家族idに紐づいたお手伝い報酬設定
        $chore = Chore::query()
            ->where('family_id', $request->user()->family_id)
            ->findOrFail($validated['chore_id']);

        // ChoreRecordServiceクラスでお手伝いの新規登録処理を行う
        $this->choreRecordService->update($choreRecord, $chore, $validated);

        return redirect()
            ->route('parent.chores.history', $child)
            ->with('success', 'お手伝い実績を更新しました。');
    }

    // お手伝い履歴の削除
    public function destroy(User $child, ChoreRecord $choreRecord): RedirectResponse
    {
        // ログイン中の保護者が、このお手伝い実績を操作してよいか確認
        Gate::authorize('manageChoreRecord', $choreRecord);
        // $this->ensureRecordBelongsToChild($child, $choreRecord);

        // ChoreRecordServiceクラスでお手伝いの削除処理を行う
        $this->choreRecordService->delete($choreRecord);

        return redirect()
            ->route('parent.chores.history', $child)
            ->with('success', 'お手伝い実績を削除しました。');
    }

    // private function ensureRecordBelongsToChild(User $child, ChoreRecord $choreRecord): void
    // {
    //     Gate::authorize('viewFamilyChild', $child);
    //     Gate::authorize('manageChoreRecord', $choreRecord);

    //     abort_unless($choreRecord->user_id === $child->id, 404);
    // }
}
