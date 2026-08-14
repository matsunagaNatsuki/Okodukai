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
    public function __construct(private readonly ChoreRecordService $choreRecordService) {}

    public function create(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        $chores = Chore::query()
            ->where('family_id', request()->user()->family_id)
            ->orderBy('chore_name')
            ->get();

        return view('parent.chores-performance.create', [
            'child' => $child,
            'chores' => $chores,
        ]);
    }

    public function store(StoreChorePerformanceRequest $request, User $child): RedirectResponse
    {
        Gate::authorize('viewFamilyChild', $child);

        $validated = $request->validated();
        $chore = Chore::query()
            ->where('family_id', $request->user()->family_id)
            ->findOrFail($validated['chore_id']);

        $this->choreRecordService->create($child, $chore, $request->user(), $validated);

        return redirect()
            ->route('parent.chores.performance', $child)
            ->with('success', 'お手伝い実績を登録しました。');
    }
}
