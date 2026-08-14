<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('registers the web application route structure', function (string $name, string $uri) {
    $route = Route::getRoutes()->getByName($name);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe($uri)
        ->and($route->methods())->toContain('GET');
})->with([
    ['register', 'parent/register'],
    ['login', 'parent/login'],
    ['parent.children.index', 'parent/children'],
    ['parent.children.show', 'parent/child/{child}'],
    ['parent.pocket-money.show', 'parent/pocket-money/{child}'],
    ['parent.chores.performance', 'parent/chores/performance/{child}'],
    ['parent.chores.history', 'parent/chores/history/{child}'],
    ['parent.child-payment.history', 'parent/child-payment/history/{child}'],
    ['parent.savings.show', 'parent/savings/{child}'],
    ['parent.family-account.index', 'parent/family-account'],
    ['parent.chores-setting.index', 'parent/chores-setting'],
    ['parent.profile.edit', 'parent/profile'],
    ['child.login', 'child/login'],
    ['child.home', 'child'],
    ['child.payment-record.create', 'child/payment-record'],
    ['child.payment-history.index', 'child/payment-history'],
    ['child.chores.history', 'child/chores/history'],
    ['child.savings.show', 'child/savings'],
    ['child.profile.edit', 'child/profile'],
]);

it('renders public authentication routes', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/parent/register',
    '/parent/login',
    '/child/login',
]);

it('renders the shared layout components', function () {
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'child-layout',
        'role' => 'child',
    ]);

    $this->actingAs($child)
        ->withSession(['success' => '保存しました'])
        ->get('/child')
        ->assertOk()
        ->assertSee('おこづかい')
        ->assertSee('メインナビゲーション')
        ->assertSee('保存しました')
        ->assertSee('common-modal')
        ->assertSee('loading-overlay');
});

it('switches navigation links for parent and child screens', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $child = User::factory()->create([
        'email' => null,
        'login_id' => 'child-navigation',
        'role' => 'child',
    ]);

    $this->actingAs($parent)->get('/parent/children')
        ->assertSee('家族アカウント')
        ->assertDontSee('つかったもの');

    $this->actingAs($child)->get('/child')
        ->assertSee('つかったもの')
        ->assertDontSee('家族アカウント');
});
