<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\ChoreSettingRequest;
use App\Models\Chore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentChoreSettingController extends Controller
{
    public function index(Request $request): View
    {
        $chores = Chore::query()
            ->where('family_id', $request->user()->family_id)
            ->orderBy('chore_name')
            ->get();

        return view('parent.chores-setting.index', [
            'chores' => $chores,
        ]);
    }

    public function store(ChoreSettingRequest $request): RedirectResponse
    {
        $request->user()->family->chores()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('parent.chores-setting.index')
            ->with('success', 'お手伝い設定を登録しました。');
    }

    public function update(ChoreSettingRequest $request, Chore $chore): JsonResponse
    {
        Gate::authorize('manageFamilyChore', $chore);

        $chore->update($request->validated());

        return response()->json([
            'message' => 'お手伝い設定を更新しました。',
            'chore' => [
                'id' => $chore->id,
                'chore_name' => $chore->chore_name,
                'reward_amount' => $chore->reward_amount,
                'reward_amount_label' => number_format($chore->reward_amount).'円',
            ],
        ]);
    }

    public function destroy(Chore $chore): JsonResponse
    {
        Gate::authorize('manageFamilyChore', $chore);

        $chore->delete();

        return response()->json([
            'message' => 'お手伝い設定を削除しました。',
        ]);
    }
}
