<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPaymentRecordChild(int $balance = 1250): User
{
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'payment-record-child',
        'role' => 'child',
    ]);

    if ($balance > 0) {
        Transaction::create([
            'user_id' => $child->id,
            'type' => 'income',
            'category' => 'allowance',
            'amount' => $balance,
            'transaction_date' => '2026-08-01',
            'title' => 'おこづかい',
            'created_by' => $child->id,
        ]);
    }

    return $child;
}

it('shows the payment form and current balance', function () {
    $child = createPaymentRecordChild();

    $this->actingAs($child)
        ->get(route('child.payment-record.create'))
        ->assertOk()
        ->assertSee('つかったものを記録')
        ->assertSee('1,250円')
        ->assertSee('使用日');
});

it('registers an expense transaction and redirects home', function () {
    $child = createPaymentRecordChild();

    $this->actingAs($child)
        ->post(route('child.payment-record.store'), [
            'title' => 'ノート',
            'amount' => 250,
            'used_at' => '2026-08-12',
        ])
        ->assertRedirect(route('child.home'))
        ->assertSessionHas('success', 'つかったものを記録しました。');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $child->id,
        'type' => 'expense',
        'category' => 'expense',
        'amount' => 250,
        'transaction_date' => '2026-08-12 00:00:00',
        'title' => 'ノート',
        'created_by' => $child->id,
    ]);
});

it('rejects an expense greater than the current balance', function () {
    $child = createPaymentRecordChild(500);

    $this->actingAs($child)
        ->from(route('child.payment-record.create'))
        ->post(route('child.payment-record.store'), [
            'title' => '高い買い物',
            'amount' => 501,
            'used_at' => '2026-08-12',
        ])
        ->assertRedirect(route('child.payment-record.create'))
        ->assertSessionHasErrors([
            'amount' => '現在残高を超える金額は登録できません。',
        ]);

    expect(Transaction::where('type', 'expense')->count())->toBe(0);
});

it('validates title amount and used date', function () {
    $child = createPaymentRecordChild();

    $this->actingAs($child)
        ->post(route('child.payment-record.store'), [
            'title' => '',
            'amount' => 0,
            'used_at' => 'invalid-date',
        ])
        ->assertSessionHasErrors(['title', 'amount', 'used_at']);

    expect(Transaction::where('type', 'expense')->count())->toBe(0);
});

it('does not allow a parent to access child payment recording', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get(route('child.payment-record.create'))
        ->assertRedirect(route('parent.children.index'));
});
