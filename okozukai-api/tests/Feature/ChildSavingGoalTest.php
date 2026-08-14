<?php

use App\Models\Family;
use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createChildForSavingGoal(): User
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => '31415926',
    ]);
    $parent->update(['family_id' => $family->id]);

    return User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'saving-child',
        'role' => 'child',
    ]);
}

function addChildSavingBalance(User $child, string $type, int $amount): void
{
    Transaction::create([
        'user_id' => $child->id,
        'type' => $type,
        'category' => $type === 'income' ? 'allowance' : 'expense',
        'amount' => $amount,
        'title' => '残高計算用',
        'created_by' => $child->id,
    ]);
}

it('shows the saving goal and calculated progress for the logged in child', function () {
    $child = createChildForSavingGoal();
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => 'ゲーム機',
        'target_amount' => 2000,
        'is_completed' => false,
    ]);
    addChildSavingBalance($child, 'income', 1500);
    addChildSavingBalance($child, 'expense', 500);

    $this->actingAs($child)
        ->get(route('child.savings.show'))
        ->assertOk()
        ->assertSee('ゲーム機')
        ->assertSee('2,000円')
        ->assertSee('1,000円')
        ->assertSee('50.0%')
        ->assertSee('style="width: 50%"', false);
});

it('creates a saving goal when one does not exist', function () {
    $child = createChildForSavingGoal();

    $this->actingAs($child)
        ->post(route('child.savings.store'), [
            'item_name' => '自転車',
            'target_amount' => 30000,
        ])
        ->assertRedirect(route('child.savings.show'))
        ->assertSessionHas('success', '貯金目標を保存しました。');

    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'item_name' => '自転車',
        'target_amount' => 30000,
        'is_completed' => false,
    ]);
});

it('updates the existing saving goal without creating another one', function () {
    $child = createChildForSavingGoal();
    SavingGoal::create([
        'user_id' => $child->id,
        'item_name' => '本',
        'target_amount' => 1000,
        'is_completed' => false,
    ]);

    $this->actingAs($child)->post(route('child.savings.store'), [
        'item_name' => '図鑑',
        'target_amount' => 2500,
    ])->assertRedirect(route('child.savings.show'));

    expect(SavingGoal::query()->where('user_id', $child->id)->count())->toBe(1);
    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'item_name' => '図鑑',
        'target_amount' => 2500,
    ]);
});

it('requires a target amount of at least one yen', function () {
    $child = createChildForSavingGoal();

    $this->actingAs($child)
        ->post(route('child.savings.store'), [
            'item_name' => 'ゲーム',
            'target_amount' => 0,
        ])
        ->assertSessionHasErrors('target_amount');

    $this->assertDatabaseCount('saving_goals', 0);
});

it('shows achievement and caps the progress bar at one hundred percent', function () {
    $child = createChildForSavingGoal();
    addChildSavingBalance($child, 'income', 1500);

    $this->actingAs($child)->post(route('child.savings.store'), [
        'item_name' => 'ボール',
        'target_amount' => 1000,
    ]);

    $this->actingAs($child)
        ->get(route('child.savings.show'))
        ->assertOk()
        ->assertSee('目標達成！')
        ->assertSee('150.0%')
        ->assertSee('style="width: 100%"', false)
        ->assertSee('0円');

    $this->assertDatabaseHas('saving_goals', [
        'user_id' => $child->id,
        'is_completed' => true,
    ]);
});
