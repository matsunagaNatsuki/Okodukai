<?php

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// テストデータの作成
function createChildChoreHistoryFamily(): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '75315982'
    ]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'chore-history-child',
        'role' => 'child',
    ]);
    // お手伝い内容ダミーデータ
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '食器洗い',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);

    return [$parent, $child, $chore];
}

// お子様のお手伝い実績の新規作成
function createChildChoreRecord(User $parent, User $child, Chore $chore): ChoreRecord
{
    return ChoreRecord::create([
        'user_id' => $child->id,
        'chore_id' => $chore->id,
        'registered_by' => $parent->id,
        'reward_amount' => 100,
        // 'performed_at' => sprintf('2026-08-%02d', $day),
    ]);
}

// お子様のお手伝いの記録データの反映に関するテスト
it('shows only the logged in childs chore records', function () {
    // テストデータの作成
    [$parent, $child, $chore] = createChildChoreHistoryFamily();
    createChildChoreRecord($parent, $child, $chore);

    // 同じ家族の兄弟のダミーユーザ
    $otherChild = User::factory()->create([
        'family_id' => $child->family_id,
        'email' => null,
        'login_id' => 'other-chore-child',
        'role' => 'child',
    ]);
    createChildChoreRecord($parent, $otherChild, $chore);

    // 現在時刻
    $today = now()->format('Y年n月j日');

    // 最初に登録したお子様のお手伝いの記録のデータが正しく反映されているか
    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee($today)
        ->assertSee('食器洗い')
        ->assertSee('+100円');
});

// お子様のお手伝いの記録のページネーションに関するテスト
it('orders chore records newest first and paginates by ten', function () {
    // ダミーユーザの作成
    [$parent, $child, $chore] = createChildChoreHistoryFamily();

    // 12件のお手伝い実績データを作成
    foreach (range(1, 12) as $i) {
        createChildChoreRecord($parent, $child, $chore);
    }

    // 現在時刻
    $today = now()->format('Y年n月j日');

    // お子様のお手伝いの記録が正しく反映されているか
    $response = $this->actingAs($child)
                ->get(route('child.chores.history'));
    $response->assertOk()->assertSee($today);

    // 2件のお手伝いの記録が正しく10件ずつページネーションされているか
    expect($response->viewData('records')->count())->toBe(10)
        ->and($response->viewData('records')->total())->toBe(12);
        // ->and($response->viewData('records')->first()->performed_at->day)->toBe(12);
});

// お子様のお手伝いのデータがなかった場合のテスト
it('shows the empty state when there are no chore records', function () {
    // お子様のテストデータのみ作成
    [, $child] = createChildChoreHistoryFamily();

    // データが存在しない文言が表示されているか
    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee('まだ行なったお手伝いがありません');
});

// お手伝い設定の削除後にデータが反映されているか確認
it('keeps displaying history after a chore setting is soft deleted', function () {
    // ダミーデータの作成
    [$parent, $child, $chore] = createChildChoreHistoryFamily();
    createChildChoreRecord($parent, $child, $chore);

    // お手伝い設定の削除
    $chore->delete();

    // 削除後、お子様のお手伝い記録のデータは維持されているか
    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee('食器洗い')
        ->assertSee('+100円');
});

// 保護者がお子様のお手伝い記録にアクセスした際のテスト
it('redirects a parent away from child chore history', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    // 保護者ユーザは保護者画面のお子様一覧にリダイレクトするか
    $this->actingAs($parent)
        ->get(route('child.chores.history'))
        ->assertRedirect(route('parent.children.index'));
});
