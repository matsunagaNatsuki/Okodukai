<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('displays the Fortify password reset request form', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('再設定用リンクを送信する');
});

it('sends a Fortify password reset link to a parent', function () {
    Notification::fake();
    $parent = User::factory()->create([
        'email' => 'parent@example.com',
        'role' => 'parent',
    ]);

    $this->post(route('password.email'), ['email' => $parent->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($parent, ResetPassword::class);
});

it('resets a parent password using a valid Fortify token', function () {
    $parent = User::factory()->create([
        'email' => 'parent@example.com',
        'role' => 'parent',
    ]);
    $token = Password::broker()->createToken($parent);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $parent->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect(route('login'))
        ->assertSessionHas('status');

    expect(Hash::check('new-password-123', $parent->fresh()->password))->toBeTrue();
});

it('does not reset a password with an invalid token', function () {
    $parent = User::factory()->create([
        'email' => 'parent@example.com',
        'role' => 'parent',
    ]);

    $this->from(route('password.reset', ['token' => 'invalid-token']))
        ->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $parent->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('email');

    expect(Hash::check('new-password-123', $parent->fresh()->password))->toBeFalse();
});
