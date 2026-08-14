<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHistoryTransaction(User $child, array $attributes): Transaction
{
    return Transaction::create(array_merge([
        'user_id' => $child->id,
        'type' => 'expense',
        'category' => 'expense',
        'amount' => 100,
        'transaction_date' => '2026-08-01',
        'title' => 'お菓子',
        'created_by' => $child->id,
    ], $attributes));
}

it('shows the logged in childs current balance and expenses only', function () {
    $child = User::factory()->create(['email' => null, 'login_id' => 'history-child', 'role' => 'child']);
    createHistoryTransaction($child, [
        'type' => 'income',
        'category' => 'allowance',
        'amount' => 1500,
        'title' => '定期おこづかい',
    ]);
    createHistoryTransaction($child, [
        'amount' => 250,
        'transaction_date' => '2026-08-12',
        'title' => '文房具',
    ]);

    $this->actingAs($child)
        ->get(route('child.payment-history.index'))
        ->assertOk()
        ->assertSee('1,250円')
        ->assertSee('2026年8月12日')
        ->assertSee('文房具')
        ->assertSee('-250円')
        ->assertDontSee('定期おこづかい');
});

it('orders expenses newest first and paginates by ten', function () {
    $child = User::factory()->create(['email' => null, 'login_id' => 'paged-child', 'role' => 'child']);

    foreach (range(1, 12) as $day) {
        createHistoryTransaction($child, [
            'amount' => $day,
            'transaction_date' => sprintf('2026-08-%02d', $day),
            'title' => "支出{$day}",
        ]);
    }

    $response = $this->actingAs($child)->get(route('child.payment-history.index'));
    $response->assertOk()->assertSee('支出12')->assertDontSee('支出1</strong>', false);

    expect($response->viewData('transactions')->count())->toBe(10)
        ->and($response->viewData('transactions')->total())->toBe(12)
        ->and($response->viewData('transactions')->first()->title)->toBe('支出12');
});

it('does not show another childs or soft deleted expenses', function () {
    $child = User::factory()->create(['email' => null, 'login_id' => 'own-child', 'role' => 'child']);
    $otherChild = User::factory()->create(['email' => null, 'login_id' => 'other-child-history', 'role' => 'child']);
    createHistoryTransaction($otherChild, ['title' => '別の子どもの支出']);
    $deleted = createHistoryTransaction($child, ['title' => '削除済み支出']);
    $deleted->delete();

    $this->actingAs($child)
        ->get(route('child.payment-history.index'))
        ->assertOk()
        ->assertSee('まだ支出履歴がありません')
        ->assertDontSee('別の子どもの支出')
        ->assertDontSee('削除済み支出');
});

it('redirects a parent away from child payment history', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get(route('child.payment-history.index'))
        ->assertRedirect(route('parent.children.index'));
});
