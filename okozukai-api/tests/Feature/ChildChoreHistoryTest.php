<?php

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createChildChoreHistoryFamily(): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create(['owner_user_id' => $parent->id, 'family_code' => '75315982']);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'chore-history-child',
        'role' => 'child',
    ]);
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '食器洗い',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);

    return [$parent, $child, $chore];
}

function createChildChoreRecord(User $parent, User $child, Chore $chore, int $day): ChoreRecord
{
    return ChoreRecord::create([
        'user_id' => $child->id,
        'chore_id' => $chore->id,
        'registered_by' => $parent->id,
        'reward_amount' => 100 + $day,
        'performed_at' => sprintf('2026-08-%02d', $day),
    ]);
}

it('shows only the logged in childs chore records', function () {
    [$parent, $child, $chore] = createChildChoreHistoryFamily();
    createChildChoreRecord($parent, $child, $chore, 12);
    $otherChild = User::factory()->create([
        'family_id' => $child->family_id,
        'email' => null,
        'login_id' => 'other-chore-child',
        'role' => 'child',
    ]);
    createChildChoreRecord($parent, $otherChild, $chore, 11);

    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee('2026年8月12日')
        ->assertSee('食器洗い')
        ->assertSee('+112円')
        ->assertDontSee('2026年8月11日');
});

it('orders chore records newest first and paginates by ten', function () {
    [$parent, $child, $chore] = createChildChoreHistoryFamily();

    foreach (range(1, 12) as $day) {
        createChildChoreRecord($parent, $child, $chore, $day);
    }

    $response = $this->actingAs($child)->get(route('child.chores.history'));
    $response->assertOk()->assertSee('2026年8月12日')->assertDontSee('2026年8月1日');

    expect($response->viewData('records')->count())->toBe(10)
        ->and($response->viewData('records')->total())->toBe(12)
        ->and($response->viewData('records')->first()->performed_at->day)->toBe(12);
});

it('shows the empty state when there are no chore records', function () {
    [, $child] = createChildChoreHistoryFamily();

    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee('まだお手伝い履歴がありません');
});

it('keeps displaying history after a chore setting is soft deleted', function () {
    [$parent, $child, $chore] = createChildChoreHistoryFamily();
    createChildChoreRecord($parent, $child, $chore, 10);
    $chore->delete();

    $this->actingAs($child)
        ->get(route('child.chores.history'))
        ->assertOk()
        ->assertSee('食器洗い')
        ->assertSee('+110円');
});

it('redirects a parent away from child chore history', function () {
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get(route('child.chores.history'))
        ->assertRedirect(route('parent.children.index'));
});
