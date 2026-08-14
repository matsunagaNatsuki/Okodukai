<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects a guest from a parent screen to parent login', function () {
    $this->get('/parent/children')
        ->assertRedirect(route('login'));
});

it('redirects a guest from a child screen to child login', function () {
    $this->get('/child')
        ->assertRedirect(route('child.login'));
});

it('returns json unauthorized responses for unauthenticated ajax requests', function () {
    $this->getJson('/parent/children')
        ->assertUnauthorized()
        ->assertJson(['message' => 'ログインの有効期限が切れました。']);

    $this->getJson('/child')
        ->assertUnauthorized()
        ->assertJson(['message' => 'ログインの有効期限が切れました。']);
});

it('allows a parent to access parent screens', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get('/parent/children')
        ->assertOk();
});

it('allows a child to access child screens', function () {
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'child01',
        'role' => 'child',
    ]);

    $this->actingAs($child)
        ->get('/child')
        ->assertOk();
});

it('redirects a parent from child screens to the parent home', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get('/child/payment-history')
        ->assertRedirect(route('parent.children.index'));
});

it('redirects a parent from child login to the parent home', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get('/child/login')
        ->assertRedirect(route('parent.children.index'));
});

it('redirects a child from parent screens to the child home', function () {
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'child01',
        'role' => 'child',
    ]);

    $this->actingAs($child)
        ->get('/parent/profile')
        ->assertRedirect(route('child.home'));
});

it('redirects a child from parent login to the child home', function () {
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'child02',
        'role' => 'child',
    ]);

    $this->actingAs($child)
        ->get('/parent/login')
        ->assertRedirect(route('child.home'));
});

it('keeps both login screens available to guests', function () {
    $this->get('/parent/login')->assertOk();
    $this->get('/child/login')->assertOk();
});
