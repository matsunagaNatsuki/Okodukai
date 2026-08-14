<?php

use App\Models\Family;
use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSavingGoalFamily(string $familyCode = '56473829'): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create(['owner_user_id' => $parent->id, 'family_code' => $familyCode]);
    $parent->update(['family_id' => $family->id]);
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'saving-'.$familyCode,
        'role' => 'child',
        'name' => '貯金花子',
    ]);

    return [$parent, $child];
}

function addSavingTransaction(User $child, User $creator, string $type, int $amount): void
{
    Transaction::create([
        'user_id' => $child->id,
        'type' => $type,
        'category' => $type === 'income' ? 'allowance' : 'expense',
        'amount' => $amount,
        'title' => '残高計算用',
        'created_by' => $creator->id,
    ]);
}

it('shows a saving goal balance remaining amount and achievement rate', function () {
    [$parent, $child] = createSavingGoalFamily();
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => '新しいゲーム',
        'target_amount' => 2000,
        'is_completed' => false,
    ]);
    addSavingTransaction($child, $parent, 'income', 1500);
    addSavingTransaction($child, $child, 'expense', 500);

    $this->actingAs($parent)
        ->get(route('parent.savings.show', $child))
        ->assertOk()
        ->assertSee('新しいゲーム')
        ->assertSee('2,000円')
        ->assertSee('1,000円')
        ->assertSee('50.0%')
        ->assertSee('style="width: 50%"', false)
        ->assertSee('確認専用');
});

it('caps the progress bar at one hundred percent', function () {
    [$parent, $child] = createSavingGoalFamily();
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => '本',
        'target_amount' => 1000,
        'is_completed' => false,
    ]);
    addSavingTransaction($child, $parent, 'income', 1500);

    $this->actingAs($parent)
        ->get(route('parent.savings.show', $child))
        ->assertOk()
        ->assertSee('150.0%')
        ->assertSee('目標達成！')
        ->assertSee('style="width: 100%"', false)
        ->assertSee('目標まであと')
        ->assertSee('0円');
});

it('shows guidance when the child has no saving goal', function () {
    [$parent, $child] = createSavingGoalFamily();

    $this->actingAs($parent)
        ->get(route('parent.savings.show', $child))
        ->assertOk()
        ->assertSee('貯金目標はまだ設定されていません');
});

it('forbids viewing a saving goal for a child in another family', function () {
    [$parent] = createSavingGoalFamily('10101010');
    [, $otherChild] = createSavingGoalFamily('20202020');

    $this->actingAs($parent)
        ->get(route('parent.savings.show', $otherChild))
        ->assertForbidden();
});
