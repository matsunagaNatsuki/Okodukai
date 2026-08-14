<?php

use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPaymentHistoryFamily(string $familyCode = '19283746'): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create(['owner_user_id' => $parent->id, 'family_code' => $familyCode]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'payment-'.$familyCode,
        'role' => 'child',
        'name' => '支出太郎',
    ]);

    return [$parent, $child];
}

function createChildTransaction(User $child, User $creator, array $attributes): Transaction
{
    $transaction = Transaction::create(array_merge([
        'user_id' => $child->id,
        'type' => 'expense',
        'category' => 'expense',
        'amount' => 100,
        'title' => 'お菓子',
        'created_by' => $creator->id,
    ], $attributes));

    if (isset($attributes['created_at'])) {
        $transaction->forceFill([
            'created_at' => $attributes['created_at'],
            'updated_at' => $attributes['created_at'],
        ])->saveQuietly();
    }

    return $transaction;
}

it('shows the child name current balance and expense transactions only', function () {
    [$parent, $child] = createPaymentHistoryFamily();
    createChildTransaction($child, $parent, [
        'type' => 'income',
        'category' => 'allowance',
        'amount' => 1500,
        'title' => '定期収入タイトル',
    ]);
    createChildTransaction($child, $child, [
        'amount' => 250,
        'title' => '文房具',
        'created_at' => '2026-08-12 10:00:00',
    ]);

    $this->actingAs($parent)
        ->get(route('parent.child-payment.history', $child))
        ->assertOk()
        ->assertSee('支出太郎さんの現在残高')
        ->assertSee('1,250円')
        ->assertSee('2026年8月12日')
        ->assertSee('文房具')
        ->assertSee('-250円')
        ->assertDontSee('定期収入タイトル');
});

it('orders expenses newest first and paginates by ten', function () {
    [$parent, $child] = createPaymentHistoryFamily();

    foreach (range(1, 12) as $day) {
        createChildTransaction($child, $child, [
            'amount' => $day,
            'title' => "支出{$day}",
            'created_at' => sprintf('2026-08-%02d 10:00:00', $day),
        ]);
    }

    $response = $this->actingAs($parent)
        ->get(route('parent.child-payment.history', $child));

    $response->assertOk()
        ->assertSee('支出12')
        ->assertDontSee('支出1</strong>', false);

    expect($response->viewData('transactions')->count())->toBe(10)
        ->and($response->viewData('transactions')->total())->toBe(12)
        ->and($response->viewData('transactions')->first()->title)->toBe('支出12');
});

it('excludes another childs expenses and soft deleted transactions', function () {
    [$parent, $child] = createPaymentHistoryFamily();
    $otherChild = User::factory()->create([
        'family_id' => $parent->family_id,
        'email' => null,
        'login_id' => 'other-payment-child',
        'role' => 'child',
    ]);
    createChildTransaction($otherChild, $otherChild, ['title' => '別の子どもの支出']);
    $deleted = createChildTransaction($child, $child, ['title' => '削除済み支出']);
    $deleted->delete();

    $this->actingAs($parent)
        ->get(route('parent.child-payment.history', $child))
        ->assertOk()
        ->assertSee('まだ支出履歴がありません')
        ->assertDontSee('別の子どもの支出')
        ->assertDontSee('削除済み支出');
});

it('forbids viewing a child payment history from another family', function () {
    [$parent] = createPaymentHistoryFamily('11113333');
    [, $otherChild] = createPaymentHistoryFamily('22224444');

    $this->actingAs($parent)
        ->get(route('parent.child-payment.history', $otherChild))
        ->assertForbidden();
});
