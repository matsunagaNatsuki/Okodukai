<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

// DBが初期状態の環境で実行
uses(RefreshDatabase::class);

// テストユーザの作成
function createFamilyWithChild(array $childAttributes = []): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '12345678',
    ]);
    $parent->update(['family_id' => $family->id]);

    $child = User::factory()->create(array_merge([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'child01',
        'password' => Hash::make('password123'),
        'role' => 'child',
    ], $childAttributes));

    return [$family, $child];
}

// お子様ログイン画面にて文字列が含まれているか
it('displays the child login form without retaining a password', function () {
    $this->get('/child/login')
        ->assertOk()
        ->assertSee('お子様ログイン')
        ->assertSee('ログイン情報を保存する')
        ->assertSee('パスワードは保存しません。');
});

// お子様ログイン処理が正常か
it('logs in a child belonging to the specified family', function () {
    // お子様テストユーザのみ削除
    [, $child] = createFamilyWithChild();
    // セッションIDを取得
    $oldSessionId = session()->getId();

    // postリクエストを送信
    $response = $this->post('/child/login', [
        'family_code' => '12345678',
        'login_id' => 'child01',
        'password' => 'password123',
    ]);

    // リクエスト送信後お子様ホーム画面にログインできているか
    $response->assertRedirect(route('child.home'))
        ->assertSessionHas('success', 'ログインしました。');

    // 指定したお子様ユーザでログインできているか確認
    $this->assertAuthenticatedAs($child);
    // ログイン前のセッションIDが削除されているか
    expect(session()->getId())->not->toBe($oldSessionId);
});

// 違う家族コードを入力した際に関するテスト
it('does not authenticate a child with a different family code', function () {
    // テストユーザ作成
    createFamilyWithChild();

    // 家族コードを違った番号でフォーム送信
    $this->from('/child/login')->post('/child/login', [
        'family_code' => '87654321',
        'login_id' => 'child01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors([
            'family_code' => '家族コード、ログインID、またはパスワードが正しくありません。',
        ]);

    // ログインしていないか
    $this->assertGuest();
});

// 保護者ユーザがお子様ログインの認証できないか
it('does not authenticate a parent through child login', function () {
    // ダミーの家族コード
    [$family] = createFamilyWithChild();
    $parent = $family->owner;
    // 家族代表保護者ユーザのログイン情報を更新
    $parent->update([
        'login_id' => 'parent01',
        'password' => Hash::make('password123'),
    ]);

    // 家族代表保護者ユーザがお子様ログインにログインできないか
    $this->from('/child/login')->post('/child/login', [
        'family_code' => '12345678',
        'login_id' => 'parent01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors('family_code');

    // ログインしていないか
    $this->assertGuest();
});

// 8桁の番号の家族コードのバリデーションに関するテスト
it('validates the eight digit family code', function () {
    // 家族コードを文字で入力した際にバリデーションが表示されるか
    $this->from('/child/login')->post('/child/login', [
        'family_code' => '1234abcd',
        'login_id' => 'child01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors('family_code');
});
