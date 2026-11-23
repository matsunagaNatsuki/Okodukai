<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// テストを行う際にDBを初期化
uses(RefreshDatabase::class);

// お子様の支出のダミーデータ
function createHistoryTransaction(User $child, array $attributes): Transaction
{
    return Transaction::create(array_merge([
        'user_id' => $child->id,
        'type' => 'expense',
        'category' => 'expense',
        'amount' => 100,
        'transaction_date' => now(),
        'title' => 'お菓子',
        'created_by' => $child->id,
    ], $attributes));
}

// お子様の収支と残高のデータに関するテスト
it('shows the logged in childs current balance and expenses only', function () {
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'history-child',
        'role' => 'child'
    ]);
    // 保護者からのおこづかい入金
    createHistoryTransaction($child, [
        'type' => 'income',
        'category' => 'allowance',
        'amount' => 1500,
        'title' => 'おこづかい入金',
    ]);
    // お子様が使ったもの
    createHistoryTransaction($child, [
        'amount' => 250,
        'transaction_date' => now()->toDateString(),
        'title' => '文房具',
    ]);

    // お子様の最近使用したお金に関するデータが正しく反映されているか
    $this->actingAs($child)
        ->get(route('child.payment-history.index'))
        ->assertOk()
        ->assertSee('1,250円') // 現在残高
        ->assertSee(now()->toDateString())
        ->assertSee('文房具') // 使用したもの
        ->assertSee('-250円') // 使用金額
        ->assertDontSee('おこづかい入金');
});

// お子様が最近使用したお金のデータのページネーションに関するテスト
it('orders expenses newest first and paginates by ten', function () {
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'paged-child',
        'role' => 'child'
    ]);

    // お子様が使用したお金のデータを12件作成
    foreach (range(1, 12) as $day) {
        createHistoryTransaction($child, [
            'amount' => $day,
            'transaction_date' => sprintf('2026-08-%02d', $day),
            'title' => "支出{$day}",
        ]);
    }

    // 12件の使用したお金のデータが反映されているか
    $response = $this->actingAs($child)
                ->get(route('child.payment-history.index'));
    $response->assertOk()
            ->assertSee('支出12')
            ->assertDontSee('支出1</strong>', false);

    // お子様が使用したお金のデータを10件ごとにページネーションできるか
    expect($response->viewData('transactions')->count())->toBe(10)
        ->and($response->viewData('transactions')->total())->toBe(12)
        // 新しいデータ順にデータが並んでいるか
        ->and($response->viewData('transactions')->first()->title)->toBe('支出12');
});

// お子様のデータごと使用したお金に関するテスト
it('does not show another childs or soft deleted expenses', function () {
    // お子様1
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'own-child',
        'role' => 'child'
    ]);
    // お子様2
    $otherChild = User::factory()->create([
        'email' => null,
        'login_id' => 'other-child-history',
        'role' => 'child'
    ]);

    // お子様2 支出のダミーデータ
    createHistoryTransaction(
        $otherChild, ['title' => 'お子様２の支出'
    ]);

    // お子様1の支出の削除
    $deleted = createHistoryTransaction(
        $child, ['title' => 'お子様１の支出の削除'
    ]);
    $deleted->delete();

    // お子様の支出データごとに正しく反映されているか
    $this->actingAs($child)
        ->get(route('child.payment-history.index'))
        ->assertOk()
        ->assertSee('まだ支出履歴がありません')
        ->assertDontSee('別の子どもの支出')
        ->assertDontSee('削除済み支出');
});

// 保護者がお子様の最近使ったお金の履歴を閲覧できないかのテスト
it('redirects a parent away from child payment history', function () {
    // 保護者ダミーユーザの作成
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get(route('child.payment-history.index'))
        // 保護者画面にリダイレクト
        ->assertRedirect(route('parent.children.index'));
});
