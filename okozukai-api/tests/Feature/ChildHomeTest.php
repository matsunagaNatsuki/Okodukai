<?php

use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// テストを行う際にDBを初期化
uses(RefreshDatabase::class);

// 収支のダミーデータ作成
function createHomeTransaction(
    User $child,
    string $type,
    int $amount,
    string $title,
    string $date
): void
{
    $transaction = Transaction::create([
        'user_id' => $child->id,
        'type' => $type,
        'category' => $type === 'income' ? 'allowance' : 'expense',
        'amount' => $amount,
        'title' => $title,
        'created_by' => $child->id,
    ]);
    // 一括代入の制限を無視してモデルイベントを発生させずにDBに保存
    $transaction->forceFill([
        'created_at' => $date,
        'updated_at' => $date
    ])->saveQuietly();
}

// お子様の収支に関するホーム画面のテスト
it('shows the logged in child balance saving goal and recent transactions', function () {
    // お子様ダミーユーザ
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'home-child',
        'name' => 'ホーム太郎',
        'role' => 'child',
    ]);
    // 目標設定ダミーデータ
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => 'ゲーム機',
        'target_amount' => 2000,
        'is_completed' => false,
    ]);

    // 現在時刻
    $date = now();

    // おこづかい入金額
    createHomeTransaction($child, 'income', 1500, 'おこづかい', $date);
    // お子様が使ったもの
    createHomeTransaction($child, 'expense', 250, '文房具', $date);

    // 使ったお金を記録した後のお子様ホーム画面の動作テスト
    $this->actingAs($child)
        ->get(route('child.home'))
        ->assertOk()
        ->assertSee('ホーム太郎さん') // お子様ユーザ名
        ->assertSee('1,250円') // 現在残高
        ->assertSee('ゲーム機') // 欲しいもの
        ->assertSee('2,000円') // 目標金額
        ->assertSee('750円') // 残金
        ->assertSee('62.5%') // 達成率
        ->assertSee('文房具') // 使ったもの
        ->assertSee('-250円') // 使った金額
        ->assertSee('おこづかいの記録')
        ->assertSee('ログアウト');
});

// お子様ホーム画面のおこづかいの記録5件に関するテスト
it('shows only the five newest active transactions', function () {
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'recent-child',
        'role' => 'child'
    ]);

    // 現在時刻
    $date = now();

    // お子様が使ったもののダミーデータを6件作成
    foreach (range(1, 6) as $day) {
        createHomeTransaction($child, 'expense', $day, "取引{$day}", $date);
    }

    // お子様ホーム画面にて正常にダミーデータが反映しているか
    $response = $this->actingAs($child)->get(route('child.home'));
    $response->assertOk()
            ->assertSee('取引6')
            ->assertDontSee('取引1</strong>', false);

    // お子様の収支の履歴が5件反映されているか
    expect($response->viewData('recentTransactions'))->toHaveCount(5);
});

// お子様プロフィール画像とデータがない時のテスト
it('uses the default profile image and shows empty states', function () {
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'empty-home-child',
        'role' => 'child',
        'profile_image' => null,
    ]);

    $this->actingAs($child)
        ->get(route('child.home'))
        ->assertOk()
        ->assertSee(asset('images/default-profile.svg'))
        ->assertSee('まだためたいお金の設定がありません。')
        ->assertSee('まだおこづかいの記録がありません。');
});

// お子様のログインに関するテスト
it('logs out a child and invalidates authentication', function () {
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'logout-child',
        'role' => 'child'
    ]);

    // 正常にログアウトができるか
    $this->actingAs($child)
        ->post(route('child.logout'))
        ->assertRedirect(route('child.login'))
        ->assertSessionHas('success', 'ログアウトしました。');

    // ユーザが未認証であるか
    $this->assertGuest();
});
