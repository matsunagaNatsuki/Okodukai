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
    // 定期おこづかいの設定
    public function edit(User $child): View
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか確認
        Gate::authorize('viewFamilyChild', $child);

        return view('parent.pocket-money.edit', [
            'child' => $child,
            'allowance' => $child->allowance,
        ]);
    }

    // 定期おこづかいの更新
    public function update(UpsertAllowanceRequest $request, User $child): RedirectResponse
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        Gate::authorize('viewFamilyChild', $child);

        // バリデーションに成功した入力データを取得
        $validated = $request->validated();

        DB::transaction(function () use ($child, $validated): void {
            $allowance = Allowance::query()
                ->where('user_id', $child->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            // バリデーション済みの値を、新しい配列にまとめる
            $attributes = [
                'amount' => $validated['amount'],
                'payment_day' => $validated['payment_day'],
                // 'is_active' => $validated['is_active'],
            ];

            // 定期おこづかい設定の新規作成
            if ($allowance === null) {
                $child->allowances()->create($attributes);

                return;
            }

            // 定期おこづかい設定の更新
            $allowance->update($attributes);
        });

        return redirect()
            ->route('parent.pocket-money.show', $child)
            ->with('success', '定期おこづかい設定を保存しました。');
    }
}
