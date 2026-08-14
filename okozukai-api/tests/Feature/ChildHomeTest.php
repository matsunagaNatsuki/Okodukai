<?php

use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHomeTransaction(User $child, string $type, int $amount, string $title, string $date): void
{
    $transaction = Transaction::create([
        'user_id' => $child->id,
        'type' => $type,
        'category' => $type === 'income' ? 'allowance' : 'expense',
        'amount' => $amount,
        'title' => $title,
        'created_by' => $child->id,
    ]);
    $transaction->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
}

it('shows the logged in child balance saving goal and recent transactions', function () {
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'home-child',
        'name' => 'ホーム太郎',
        'role' => 'child',
    ]);
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => 'ゲーム機',
        'target_amount' => 2000,
        'is_completed' => false,
    ]);
    createHomeTransaction($child, 'income', 1500, 'おこづかい', '2026-08-10 10:00:00');
    createHomeTransaction($child, 'expense', 250, '文房具', '2026-08-11 10:00:00');

    $this->actingAs($child)
        ->get(route('child.home'))
        ->assertOk()
        ->assertSee('ホーム太郎さん')
        ->assertSee('1,250円')
        ->assertSee('ゲーム機')
        ->assertSee('2,000円')
        ->assertSee('750円')
        ->assertSee('62.5%')
        ->assertSee('文房具')
        ->assertSee('-250円')
        ->assertSee('つかったものを記録')
        ->assertSee('ログアウト');
});

it('shows only the five newest active transactions', function () {
    $child = User::factory()->create(['email' => null, 'login_id' => 'recent-child', 'role' => 'child']);

    foreach (range(1, 6) as $day) {
        createHomeTransaction($child, 'expense', $day, "取引{$day}", sprintf('2026-08-%02d 10:00:00', $day));
    }

    $response = $this->actingAs($child)->get(route('child.home'));
    $response->assertOk()->assertSee('取引6')->assertDontSee('取引1</strong>', false);
    expect($response->viewData('recentTransactions'))->toHaveCount(5);
});

it('uses the default profile image and shows empty states', function () {
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
        ->assertSee('まだ貯金目標がありません。')
        ->assertSee('まだ取引履歴がありません。');
});

it('logs out a child and invalidates authentication', function () {
    $child = User::factory()->create(['email' => null, 'login_id' => 'logout-child', 'role' => 'child']);

    $this->actingAs($child)
        ->post(route('child.logout'))
        ->assertRedirect(route('child.login'))
        ->assertSessionHas('success', 'ログアウトしました。');

    $this->assertGuest();
});
