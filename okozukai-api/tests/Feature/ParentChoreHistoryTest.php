<?php

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHistoryFamily(string $familyCode = '10293847'): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create(['owner_user_id' => $parent->id, 'family_code' => $familyCode]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'history-'.$familyCode,
        'role' => 'child',
    ]);
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '食器洗い',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);

    return [$parent, $family, $child, $chore];
}

function createLinkedHistory(User $parent, User $child, Chore $chore, array $attributes = []): array
{
    $record = ChoreRecord::create(array_merge([
        'user_id' => $child->id,
        'chore_id' => $chore->id,
        'registered_by' => $parent->id,
        'reward_amount' => 100,
        'performed_at' => '2026-08-01',
    ], $attributes));
    $transaction = Transaction::create([
        'chore_record_id' => $record->id,
        'user_id' => $child->id,
        'type' => 'income',
        'category' => 'chore',
        'amount' => $record->reward_amount,
        'title' => $chore->chore_name,
        'created_by' => $parent->id,
    ]);

    return [$record, $transaction];
}

it('shows newest chore records with ten records per page', function () {
    [$parent, , $child, $chore] = createHistoryFamily();

    foreach (range(1, 12) as $day) {
        createLinkedHistory($parent, $child, $chore, [
            'reward_amount' => 100 + $day,
            'performed_at' => sprintf('2026-08-%02d', $day),
        ]);
    }

    $response = $this->actingAs($parent)->get(route('parent.chores.history', $child));

    $response->assertOk()
        ->assertSee('2026年8月12日')
        ->assertSee('+112円')
        ->assertDontSee('2026年8月1日');

    expect($response->viewData('records')->count())->toBe(10)
        ->and($response->viewData('records')->total())->toBe(12);
});

it('updates the chore record and its linked transaction together', function () {
    [$parent, $family, $child, $chore] = createHistoryFamily();
    $newChore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => 'お風呂掃除',
        'reward_amount' => 300,
        'created_by' => $parent->id,
    ]);
    [$record, $transaction] = createLinkedHistory($parent, $child, $chore);

    $this->actingAs($parent)
        ->put(route('parent.chores.history.update', [$child, $record]), [
            'chore_id' => $newChore->id,
            'reward_amount' => 350,
            'performed_at' => '2026-08-10',
        ])
        ->assertRedirect(route('parent.chores.history', $child))
        ->assertSessionHas('success', 'お手伝い実績を更新しました。');

    expect($record->fresh()->chore_id)->toBe($newChore->id)
        ->and($record->fresh()->reward_amount)->toBe(350)
        ->and($transaction->fresh()->amount)->toBe(350)
        ->and($transaction->fresh()->title)->toBe('お風呂掃除');
});

it('deletes the record and soft deletes its linked income transaction', function () {
    [$parent, , $child, $chore] = createHistoryFamily();
    [$record, $transaction] = createLinkedHistory($parent, $child, $chore);

    $this->actingAs($parent)
        ->delete(route('parent.chores.history.destroy', [$child, $record]))
        ->assertRedirect(route('parent.chores.history', $child))
        ->assertSessionHas('success', 'お手伝い実績を削除しました。');

    $this->assertDatabaseMissing('chore_records', ['id' => $record->id]);
    $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
});

it('rolls back a record update when transaction update fails', function () {
    [$parent, , $child, $chore] = createHistoryFamily();
    [$record] = createLinkedHistory($parent, $child, $chore);
    Transaction::updating(fn () => throw new RuntimeException('update failed'));
    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($parent)->put(
        route('parent.chores.history.update', [$child, $record]),
        ['chore_id' => $chore->id, 'reward_amount' => 900, 'performed_at' => '2026-08-11'],
    ))->toThrow(RuntimeException::class, 'update failed');

    expect($record->fresh()->reward_amount)->toBe(100);
});

it('forbids editing a chore record from another family', function () {
    [$parent] = createHistoryFamily('11112222');
    [$otherParent, , $otherChild, $otherChore] = createHistoryFamily('33334444');
    [$otherRecord] = createLinkedHistory($otherParent, $otherChild, $otherChore);

    $this->actingAs($parent)
        ->put(route('parent.chores.history.update', [$otherChild, $otherRecord]), [
            'chore_id' => $otherChore->id,
            'reward_amount' => 999,
            'performed_at' => '2026-08-10',
        ])
        ->assertForbidden();

    expect($otherRecord->fresh()->reward_amount)->toBe(100);
});
