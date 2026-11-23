<?php

use App\Models\Family;
use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// テストを行う際にDBを初期状態にする
uses(RefreshDatabase::class);

// ダミーユーザの作成
function createChildForSavingGoal(): User
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '31415926',
    ]);
    $parent->update(['family_id' => $family->id]);

    return User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'saving-child',
        'role' => 'child',
    ]);
}

// 収支テーブルの新規作成
function addChildSavingBalance(User $child, string $type, int $amount): void
{
    Transaction::create([
        'user_id' => $child->id,
        'type' => $type,
        'category' => $type === 'income' ? 'allowance' : 'expense',
        'amount' => $amount,
        'title' => '残高計算用',
        'created_by' => $child->id,
    ]);
}

// 貯金目標の収支データに関するテスト
it('shows the saving goal and calculated progress for the logged in child', function () {
    // お子様のダミーユーザの作成
    $child = createChildForSavingGoal();
    // 貯金目標の新規作成
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => 'ゲーム機',
        'target_amount' => 2000,
        'is_completed' => false,
    ]);
    // 保護者からのおこづかい入金のデータ
    addChildSavingBalance($child, 'income', 1500);
    // お子様が使った金額
    addChildSavingBalance($child, 'expense', 500);

    // お子様貯金目標画面でデータが正しく反映されているか
    $this->actingAs($child)
        ->get(route('child.savings.show'))
        ->assertOk()
        ->assertSee('ゲーム機')
        ->assertSee('2,000円')
        ->assertSee('1,000円')
        ->assertSee('50.0%')
        ->assertSee('style="width: 50%"', false);
});

// 貯金目標設定後のデータ保存に関するテスト
it('creates a saving goal when one does not exist', function () {
    // お子様ダミーユーザ
    $child = createChildForSavingGoal();

    // 貯金目標設定後、データが正しく保存されているか
    $this->actingAs($child)
        ->post(route('child.savings.store'), [
            'item_name' => '自転車',
            'target_amount' => 30000,
        ])
        ->assertRedirect(route('child.savings.show'))
        ->assertSessionHas('success', '貯金目標を保存しました。');

    // 貯金目標テーブルに正しくデータが保存されているか
    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'item_name' => '自転車',
        'target_amount' => 30000,
        'is_completed' => false,
    ]);
});

// 貯金目標のテーブルのデータに関するテスト
it('updates the existing saving goal without creating another one', function () {
    // お子様ダミーユーザの作成
    $child = createChildForSavingGoal();
    // 貯金目標のダミーデータ
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => '本',
        'target_amount' => 1000,
        'is_completed' => false,
    ]);

    // 貯金目標が正しく更新されているか
    $this->actingAs($child)->post(route('child.savings.store'), [
        'item_name' => '図鑑',
        'target_amount' => 2500,
    ])->assertRedirect(route('child.savings.show'));

    // 貯金目標のカラムが1件のみ作成されているか
    expect(SavingGoal::query()
        ->where('user_id', $child->id)
        ->count())->toBe(1);

    // 貯金目標テーブルが更新できているかのテスト
    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'item_name' => '図鑑',
        'target_amount' => 2500,
    ]);
});

// 目標金額のバリデーションに関するテスト
it('requires a target amount of at least one yen', function () {
    // お子様ダミーユーザ
    $child = createChildForSavingGoal();

    // 目標金額が0円に設定した時の動作テスト
    $this->actingAs($child)
        ->post(route('child.savings.store'), [
            'item_name' => 'ゲーム',
            'target_amount' => 0,
        ])
        // バリデーションエラーが表示されるか
        ->assertSessionHasErrors('target_amount');

    // 貯金目標データが新規作成されていないか
    $this->assertDatabaseCount('saving_goals', 0);
});

// 
it('shows achievement and caps the progress bar at one hundred percent', function () {
    // お子様のダミーユーザの作成
    $child = createChildForSavingGoal();
    // おこづかい入金のダミー設定
    addChildSavingBalance($child, 'income', 1500);

    // 貯金目標の作成
    $this->actingAs($child)->post(route('child.savings.store'), [
        'item_name' => 'ボール',
        'target_amount' => 1000,
    ]);

    // 目標金額が達成できている画面に切り替わるか動作確認
    $this->actingAs($child)
        ->get(route('child.savings.show'))
        ->assertOk()
        ->assertSee('やったね！ぜんぶたまったよ！🎉')
        ->assertSee('150.0%')
        ->assertSee('style="width: 100%"', false)
        ->assertSee('0円');

    // DBのデータが達成できているか(trueか)
    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'is_completed' => true,
    ]);
});
