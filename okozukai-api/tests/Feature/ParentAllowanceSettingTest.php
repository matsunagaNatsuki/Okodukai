<?php

use App\Models\Allowance;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAllowanceFamily(): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '81818181',
    ]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'allowance-child',
        'role' => 'child',
        'name' => 'おこづかい太郎',
    ]);

    return [$parent, $child];
}

it('displays an existing allowance setting', function () {
    [$parent, $child] = createAllowanceFamily();
    Allowance::create([
        'user_id' => $child->id,
        'amount' => 1500,
        'payment_day' => 10,
        'is_active' => true,
    ]);

    $this->actingAs($parent)
        ->get(route('parent.pocket-money.show', $child))
        ->assertOk()
        ->assertSee('おこづかい太郎')
        ->assertSee('value="1500"', false)
        ->assertSee('value="10" selected', false);
});

it('creates a new allowance without creating a transaction', function () {
    [$parent, $child] = createAllowanceFamily();

    $this->actingAs($parent)
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 1000,
            'payment_day' => 15,
            'is_active' => '1',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child))
        ->assertSessionHas('success', '定期おこづかい設定を保存しました。');

    $this->assertDatabaseHas('allowances', [
        'user_id' => $child->id,
        'amount' => 1000,
        'payment_day' => 15,
        'is_active' => true,
    ]);
    expect(Transaction::count())->toBe(0);
});

it('updates the existing allowance instead of creating another record', function () {
    [$parent, $child] = createAllowanceFamily();
    $allowance = Allowance::create([
        'user_id' => $child->id,
        'amount' => 500,
        'payment_day' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($parent)
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 2000,
            'payment_day' => 25,
            'is_active' => '0',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child));

    expect(Allowance::count())->toBe(1)
        ->and($allowance->fresh()->amount)->toBe(2000)
        ->and($allowance->fresh()->payment_day)->toBe(25)
        ->and($allowance->fresh()->is_active)->toBeFalse()
        ->and(Transaction::count())->toBe(0);
});

it('validates the allowance amount and payment day', function () {
    [$parent, $child] = createAllowanceFamily();

    $this->actingAs($parent)
        ->from(route('parent.pocket-money.show', $child))
        ->put(route('parent.pocket-money.update', $child), [
            'amount' => 0,
            'payment_day' => 32,
            'is_active' => '1',
        ])
        ->assertRedirect(route('parent.pocket-money.show', $child))
        ->assertSessionHasErrors(['amount', 'payment_day']);

    expect(Allowance::count())->toBe(0);
});

it('forbids changing an allowance for a child in another family', function () {
    [$parent] = createAllowanceFamily();

    $otherParent = User::factory()->create(['role' => 'parent']);
    $otherFamily = Family::create([
        'owner_user_id' => $otherParent->id,
        'family_code' => '91919191',
    ]);
    $otherParent->update(['family_id' => $otherFamily->id]);
    $otherChild = User::factory()->create([
        'family_id' => $otherFamily->id,
        'email' => null,
        'login_id' => 'other-allowance-child',
        'role' => 'child',
    ]);

    $this->actingAs($parent)
        ->put(route('parent.pocket-money.update', $otherChild), [
            'amount' => 1000,
            'payment_day' => 10,
            'is_active' => '1',
        ])
        ->assertForbidden();

    expect(Allowance::count())->toBe(0);
});
