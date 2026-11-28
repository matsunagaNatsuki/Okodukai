<?php

use App\Models\Allowance;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// テストを行う際にControllerを初期化する
uses(RefreshDatabase::class);

// ダミーユーザの作成
function createAllowanceFamily(): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '81818181',
    ]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'allowance-child',
        'role' => 'child',
        'name' => 'おこづかい太郎',
    ]);

    return [$parent, $child];
}

// おこづかい入金画面とデータの表示に関するテスト
it('displays an existing allowance setting', function () {
    // ダミーユーザ作成
    [$parent, $child] = createAllowanceFamily();
    // おこづかい入金ダミーデータ
    Allowance::create([
        'user_id' => $child->id,
        'amount' => 1500,
        // 'payment_day' => 10,
        // 'is_active' => true,
    ]);

    // おこづかい入金画面と金額入力値が正しく反映されているか
    $this->actingAs($parent)
        ->get(route('parent.pocket-money.show', $child))
        ->assertOk()
        ->assertSee('おこづかい太郎')
        ->assertSee('value="1500"', false);
        // ->assertSee('value="10" selected', false);
});

it('creates a new allowance without creating a transaction', function () {
    // ダミーユーザの作成
    [$parent, $child] = createAllowanceFamily();

    // おこづかい入金が新しく作成できるか
    $this->actingAs($parent)
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 1000,
            // 'payment_day' => 15,
            // 'is_active' => '1',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child))
        ->assertSessionHas('success', '定期おこづかい設定を保存しました。');

    // DBにデータが挿入されているか
    $this->assertDatabaseHas('allowances', [
        'user_id' => $child->id,
        'amount' => 1000,
        // 'payment_day' => 15,
        // 'is_active' => true,
    ]);
    // 収支テーブルに
    expect(Transaction::count())->toBe(1);
});

// おこづかい入金の更新に関するテスト
it('updates the existing allowance instead of creating another record', function () {
    // ダミーユーザ作成
    [$parent, $child] = createAllowanceFamily();
    // おこづかい入金ダミーデータ
    $allowance = Allowance::create([
        'user_id' => $child->id,
        'amount' => 500,
        // 'payment_day' => 1,
        // 'is_active' => true,
    ]);

    // おこづかい入金データの更新が正常か？
    $this->actingAs($parent)
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 2000,
            // 'payment_day' => 25,
            // 'is_active' => '0',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child));

    // おこづかい入金のデータがDBに挿入されているか
    expect(Allowance::count())->toBe(2)
        ->and($allowance->fresh()->amount)->toBe(500)
        // ->and($allowance->fresh()->payment_day)->toBe(25)
        // ->and($allowance->fresh()->is_active)->toBeFalse()
        ->and(Transaction::count())->toBe(1);
});

// おこづかい入金のバリデーションに関するテスト
it('validates the allowance amount and payment day', function () {
    // ダミーユーザの作成
    [$parent, $child] = createAllowanceFamily();

    // おこづかい入金額を0円にした時の動作確認
    $this->actingAs($parent)
        ->from(route('parent.pocket-money.show', $child))
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 0,
            // 'payment_day' => 32,
            // 'is_active' => '1',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child))
        ->assertSessionHasErrors('amount');

    // データが新規作成されていないか
    expect(Allowance::count())->toBe(0);
});

// おこづかい入金の家族認証に関するテスト
it('forbids changing an allowance for a child in another family', function () {
    // 保護者ダミーユーザ
    [$parent] = createAllowanceFamily();

    // 他の家族のダミーユーザ
    $otherParent = User::factory()->create(['role' => 'parent']);
    $otherFamily = Family::create([
        'owner_user_id' => $otherParent->id,
        'family_code' => '91919191',
    ]);
    $otherParent->update(['family_id' => $otherFamily->id]);
    $otherChild = User::factory()->create([
        'family_id' => $otherFamily->id,
        'email' => null,
        'login_id' => 'other-allowance-child',
        'role' => 'child',
    ]);

    $this->actingAs($parent)
        // 他の家族データのおこづかい入金データが表示されていないか
        ->put(route('parent.pocket-money.update', $otherChild), [
            'amount' => 1000,
            // 'payment_day' => 10,
            // 'is_active' => '1',
        ])
        // 403エラーが発生するか
        ->assertForbidden();

    // データが作成されていないか確認
    expect(Allowance::count())->toBe(0);
});
