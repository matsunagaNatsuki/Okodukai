<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\UpsertAllowanceRequest;
use App\Models\Allowance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentPocketMoneyController extends Controller
{
    public function edit(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        return view('parent.pocket-money.edit', [
            'child' => $child,
            'allowance' => $child->allowance,
        ]);
    }

    public function update(UpsertAllowanceRequest $request, User $child): RedirectResponse
    {
        Gate::authorize('viewFamilyChild', $child);

        $validated = $request->validated();

        DB::transaction(function () use ($child, $validated): void {
            $allowance = Allowance::query()
                ->where('user_id', $child->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $attributes = [
                'amount' => $validated['amount'],
                'payment_day' => $validated['payment_day'],
                'is_active' => $validated['is_active'],
            ];

            if ($allowance === null) {
                $child->allowances()->create($attributes);

                return;
            }

            $allowance->update($attributes);
        });

        return redirect()
            ->route('parent.pocket-money.show', $child)
            ->with('success', '定期おこづかい設定を保存しました。');
    }
}
