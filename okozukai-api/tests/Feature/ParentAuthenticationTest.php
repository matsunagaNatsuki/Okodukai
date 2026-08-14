<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('displays the parent login form', function () {
    $this->get('/parent/login')
        ->assertOk()
        ->assertSee('保護者ログイン')
        ->assertSee('メールアドレスを保存する');
});

it('logs in a parent using session authentication', function () {
    $parent = User::factory()->create([
        'email' => 'parent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'parent',
    ]);

    $oldSessionId = session()->getId();

    $response = $this->post('/parent/login', [
        'email' => 'parent@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('parent.children.index'))
        ->assertSessionHas('success', 'ログインしました。');

    $this->assertAuthenticatedAs($parent);
    expect(session()->getId())->not->toBe($oldSessionId);
});

it('does not allow a child to use parent login', function () {
    User::factory()->create([
        'email' => 'child@example.com',
        'login_id' => 'child01',
        'password' => Hash::make('password123'),
        'role' => 'child',
    ]);

    $this->from('/parent/login')->post('/parent/login', [
        'email' => 'child@example.com',
        'password' => 'password123',
    ])->assertRedirect('/parent/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('shows a clear error when parent credentials are incorrect', function () {
    User::factory()->create([
        'email' => 'parent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'parent',
    ]);

    $this->from('/parent/login')->post('/parent/login', [
        'email' => 'parent@example.com',
        'password' => 'wrong-password',
    ])->assertRedirect('/parent/login')
        ->assertSessionHasErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。保護者用アカウントをご確認ください。',
        ]);

    $this->assertGuest();
});

it('stores the parent email address when requested', function () {
    User::factory()->create([
        'email' => 'parent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'parent',
    ]);

    $this->post('/parent/login', [
        'email' => 'parent@example.com',
        'password' => 'password123',
        'save_email' => '1',
    ])->assertCookie('saved_parent_email');
});

it('logs out the authenticated parent and invalidates authentication', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->post('/parent/logout')
        ->assertRedirect(route('login'))
        ->assertSessionHas('success', 'ログアウトしました。');

    $this->assertGuest();
});
