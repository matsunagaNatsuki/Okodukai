<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

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

it('displays the child login form without retaining a password', function () {
    $this->get('/child/login')
        ->assertOk()
        ->assertSee('子どもログイン')
        ->assertSee('ログイン情報を保存する')
        ->assertSee('パスワードは保存しません。');
});

it('logs in a child belonging to the specified family', function () {
    [, $child] = createFamilyWithChild();
    $oldSessionId = session()->getId();

    $response = $this->post('/child/login', [
        'family_code' => '12345678',
        'login_id' => 'child01',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('child.home'))
        ->assertSessionHas('success', 'ログインしました。');

    $this->assertAuthenticatedAs($child);
    expect(session()->getId())->not->toBe($oldSessionId);
});

it('does not authenticate a child with a different family code', function () {
    createFamilyWithChild();

    $this->from('/child/login')->post('/child/login', [
        'family_code' => '87654321',
        'login_id' => 'child01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors([
            'family_code' => '家族コード、ログインID、またはパスワードが正しくありません。',
        ]);

    $this->assertGuest();
});

it('does not authenticate a parent through child login', function () {
    [$family] = createFamilyWithChild();
    $parent = $family->owner;
    $parent->update([
        'login_id' => 'parent01',
        'password' => Hash::make('password123'),
    ]);

    $this->from('/child/login')->post('/child/login', [
        'family_code' => '12345678',
        'login_id' => 'parent01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors('family_code');

    $this->assertGuest();
});

it('validates the eight digit family code', function () {
    $this->from('/child/login')->post('/child/login', [
        'family_code' => '1234abcd',
        'login_id' => 'child01',
        'password' => 'password123',
    ])->assertRedirect('/child/login')
        ->assertSessionHasErrors('family_code');
});
