<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// テストを行う際にDBを初期化する
uses(RefreshDatabase::class);

function createPaymentRecordChild(int $balance = 1250): User
{
    // お子様ダミーユーザの作成
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'payment-record-child',
        'role' => 'child',
    ]);

    // おこづかい入金をされていたら
    if ($balance > 0) {
        Transaction::create([
            'user_id' => $child->id,
            'type' => 'income',
            'category' => 'allowance',
            'amount' => $balance,
            'transaction_date' => now()->toDateString(),
            'title' => 'おこづかい',
            'created_by' => $child->id,
        ]);
    }

    return $child;
}

// お子様の使ったお金の記録のデータ表示に関するテスト
it('shows the payment form and current balance', function () {
    // ダミーデータの作成
    $child = createPaymentRecordChild();

    // 画面とデータが正しく表示されるか
    $this->actingAs($child)
        ->get(route('child.payment-record.create'))
        ->assertOk()
        ->assertSee('つかったものを記録')
        ->assertSee('1,250円');
});

// お子様が使用したお金のデータ保存に関するテスト
it('registers an expense transaction and redirects home', function () {
    // ダミーデータの作成
    $child = createPaymentRecordChild();

    // お子様の使用したお金のデータを正しく保存できるか
    $this->actingAs($child)
        ->post(route('child.payment-record.store'), [
            'title' => 'ノート',
            'amount' => 250,
            // 'used_at' => '2026-08-12',
        ])
        ->assertRedirect(route('child.home'))
        ->assertSessionHas('success', 'つかったものを記録しました。');

    // 収支テーブルに正しく保存できているか
    $this->assertDatabaseHas('transactions', [
        'user_id' => $child->id,
        'type' => 'expense',
        'category' => 'expense',
        'amount' => 250,
        'transaction_date' => null,
        'title' => 'ノート',
        'created_by' => $child->id,
    ]);
});

// 現在残高を超えて使ったお金を記録する際のテスト
it('rejects an expense greater than the current balance', function () {
    $child = createPaymentRecordChild(500);

    // 現在残高よりも高い買い物をした時の動作テスト
    $this->actingAs($child)
        ->from(route('child.payment-record.create'))
        ->post(route('child.payment-record.store'), [
            'title' => '高い買い物',
            'amount' => 501,
            // 'used_at' => '2026-08-12',
        ])
        // バリデーションエラーが表示されるか
        ->assertRedirect(route('child.payment-record.create'))
        ->assertSessionHasErrors([
            'amount' => '現在残高を超える金額は登録できません。',
        ]);

    // 使ったお金が記録されていないか
    expect(Transaction::where('type', 'expense')->count())->toBe(0);
});

// お子様が使ったお金を記録する際のバリデーションに関するテスト
it('validates title amount and used date', function () {
    $child = createPaymentRecordChild();

    // 使ったお金のデータを空欄にした時の動作テスト
    $this->actingAs($child)
        ->post(route('child.payment-record.store'), [
            'title' => '',
            'amount' => 0,
            // 'used_at' => 'invalid-date',
        ])
        // バリデーションが表示できているか
        ->assertSessionHasErrors(['title', 'amount']);

    // 使ったお金が記録されていないか
    expect(Transaction::where('type', 'expense')->count())->toBe(0);
});

// 保護者がお子様の使ったお金の履歴を閲覧できないかのテスト
it('does not allow a parent to access child payment recording', function () {
    // 保護者ダミーユーザの作成
    $parent = User::factory()->create(['role' => 'parent']);

    // 保護者が使ったお金の履歴にアクセスするとお子様一覧画面にリダイレクト
    $this->actingAs($parent)
        ->get(route('child.payment-record.create'))
        ->assertRedirect(route('parent.children.index'));
});
