<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('displays the parent registration form', function () {
    $this->get('/parent/register')
        ->assertOk()
        ->assertSee('保護者新規登録')
        ->assertSee('家族を作成して登録する');
});

it('registers the first parent and family then logs the parent in', function () {
    $response = $this->post('/parent/register', [
        'name' => '山田 花子',
        'email' => 'parent@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $parent = User::where('email', 'parent@example.com')->firstOrFail();
    $family = Family::firstOrFail();

    $response->assertRedirect(route('parent.children.index'))
        ->assertSessionHas('success');

    expect($parent->role)->toBe('parent')
        ->and($parent->family_id)->toBe($family->id)
        ->and($family->owner_user_id)->toBe($parent->id)
        ->and($family->family_code)->toMatch('/^\d{8}$/')
        ->and(Hash::check('password123', $parent->password))->toBeTrue();

    $this->assertAuthenticatedAs($parent);
});

it('validates parent registration input without creating records', function () {
    $response = $this->from('/parent/register')->post('/parent/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertRedirect('/parent/register')
        ->assertSessionHasErrors(['name', 'email', 'password']);

    expect(User::count())->toBe(0)
        ->and(Family::count())->toBe(0);

    $this->assertGuest();
});
