<?php

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createChorePerformanceFamily(string $familyCode = '13572468'): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => $familyCode,
    ]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'performance-child-'.$familyCode,
        'role' => 'child',
        'name' => 'お手伝い花子',
    ]);
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '食器洗い',
        'reward_amount' => 120,
        'created_by' => $parent->id,
    ]);

    return [$parent, $family, $child, $chore];
}

it('shows only chore settings from the authenticated parents family', function () {
    [$parent, , $child] = createChorePerformanceFamily('13572468');
    [$otherParent, $otherFamily] = createChorePerformanceFamily('24681357');
    Chore::create([
        'family_id' => $otherFamily->id,
        'chore_name' => '別家族のお手伝い',
        'reward_amount' => 999,
        'created_by' => $otherParent->id,
    ]);

    $this->actingAs($parent)
        ->get(route('parent.chores.performance', $child))
        ->assertOk()
        ->assertSee('お手伝い花子')
        ->assertSee('食器洗い')
        ->assertSee('data-reward-amount="120"', false)
        ->assertDontSee('別家族のお手伝い');
});

it('creates a chore record and income transaction atomically', function () {
    [$parent, , $child, $chore] = createChorePerformanceFamily();

    $this->actingAs($parent)
        ->post(route('parent.chores.performance.store', $child), [
            'chore_id' => $chore->id,
            'reward_amount' => 150,
            'performed_at' => '2026-08-12',
        ])
        ->assertRedirect(route('parent.chores.performance', $child))
        ->assertSessionHas('success', 'お手伝い実績を登録しました。');

    $this->assertDatabaseHas('chore_records', [
        'user_id' => $child->id,
        'chore_id' => $chore->id,
        'registered_by' => $parent->id,
        'reward_amount' => 150,
        'performed_at' => '2026-08-12 00:00:00',
    ]);
    $this->assertDatabaseHas('transactions', [
        'user_id' => $child->id,
        'type' => 'income',
        'category' => 'chore',
        'amount' => 150,
        'title' => '食器洗い',
        'created_by' => $parent->id,
    ]);
});

it('rolls back the chore record when transaction creation fails', function () {
    [$parent, , $child, $chore] = createChorePerformanceFamily();
    Transaction::creating(fn () => throw new RuntimeException('transaction failed'));
    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($parent)
        ->post(route('parent.chores.performance.store', $child), [
            'chore_id' => $chore->id,
            'reward_amount' => 150,
            'performed_at' => '2026-08-12',
        ]))->toThrow(RuntimeException::class, 'transaction failed');

    expect(ChoreRecord::count())->toBe(0)
        ->and(Transaction::count())->toBe(0);
});

it('rejects a chore setting from another family', function () {
    [$parent, , $child] = createChorePerformanceFamily('11223344');
    [, , , $otherChore] = createChorePerformanceFamily('44332211');

    $this->actingAs($parent)
        ->from(route('parent.chores.performance', $child))
        ->post(route('parent.chores.performance.store', $child), [
            'chore_id' => $otherChore->id,
            'reward_amount' => 500,
            'performed_at' => '2026-08-12',
        ])
        ->assertRedirect(route('parent.chores.performance', $child))
        ->assertSessionHasErrors('chore_id');

    expect(ChoreRecord::count())->toBe(0)
        ->and(Transaction::count())->toBe(0);
});

it('validates the reward and performed date', function () {
    [$parent, , $child, $chore] = createChorePerformanceFamily();

    $this->actingAs($parent)
        ->post(route('parent.chores.performance.store', $child), [
            'chore_id' => $chore->id,
            'reward_amount' => 0,
            'performed_at' => 'not-a-date',
        ])
        ->assertSessionHasErrors(['reward_amount', 'performed_at']);

    expect(ChoreRecord::count())->toBe(0)
        ->and(Transaction::count())->toBe(0);
});

it('forbids registering performance for a child in another family', function () {
    [$parent] = createChorePerformanceFamily('55667788');
    [, , $otherChild, $otherChore] = createChorePerformanceFamily('88776655');

    $this->actingAs($parent)
        ->post(route('parent.chores.performance.store', $otherChild), [
            'chore_id' => $otherChore->id,
            'reward_amount' => 100,
            'performed_at' => '2026-08-12',
        ])
        ->assertForbidden();

    expect(ChoreRecord::count())->toBe(0)
        ->and(Transaction::count())->toBe(0);
});
