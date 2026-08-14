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
    public function __construct(private readonly ChoreRecordService $choreRecordService) {}

    public function index(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        $records = $child->choreRecords()
            ->with(['chore', 'transaction'])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->paginate(10);

        $chores = Chore::query()
            ->where('family_id', request()->user()->family_id)
            ->orderBy('chore_name')
            ->get();

        return view('parent.chores-history.index', compact('child', 'records', 'chores'));
    }

    public function update(
        UpdateChoreRecordRequest $request,
        User $child,
        ChoreRecord $choreRecord,
    ): RedirectResponse {
        $this->ensureRecordBelongsToChild($child, $choreRecord);
        $validated = $request->validated();

        $chore = Chore::query()
            ->where('family_id', $request->user()->family_id)
            ->findOrFail($validated['chore_id']);
        $this->choreRecordService->update($choreRecord, $chore, $validated);

        return redirect()
            ->route('parent.chores.history', $child)
            ->with('success', 'お手伝い実績を更新しました。');
    }

    public function destroy(User $child, ChoreRecord $choreRecord): RedirectResponse
    {
        Gate::authorize('manageChoreRecord', $choreRecord);
        $this->ensureRecordBelongsToChild($child, $choreRecord);

        $this->choreRecordService->delete($choreRecord);

        return redirect()
            ->route('parent.chores.history', $child)
            ->with('success', 'お手伝い実績を削除しました。');
    }

    private function ensureRecordBelongsToChild(User $child, ChoreRecord $choreRecord): void
    {
        Gate::authorize('viewFamilyChild', $child);
        Gate::authorize('manageChoreRecord', $choreRecord);

        abort_unless($choreRecord->user_id === $child->id, 404);
    }
}
