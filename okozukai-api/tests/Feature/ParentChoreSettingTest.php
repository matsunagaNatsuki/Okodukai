<?php

use App\Models\Chore;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createChoreFamily(string $familyCode = '12121212'): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => $familyCode,
    ]);
    $parent->update(['family_id' => $family->id]);

    return [$parent, $family];
}

it('shows only chores belonging to the authenticated parents family', function () {
    [$parent, $family] = createChoreFamily('12121212');
    [$otherParent, $otherFamily] = createChoreFamily('34343434');

    Chore::create([
        'family_id' => $family->id,
        'chore_name' => '食器洗い',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);
    Chore::create([
        'family_id' => $otherFamily->id,
        'chore_name' => '別家族の掃除',
        'reward_amount' => 999,
        'created_by' => $otherParent->id,
    ]);

    $this->actingAs($parent)
        ->get(route('parent.chores-setting.index'))
        ->assertOk()
        ->assertSee('食器洗い')
        ->assertSee('100円')
        ->assertDontSee('別家族の掃除')
        ->assertDontSee('999円');
});

it('creates a chore for the authenticated parents family', function () {
    [$parent, $family] = createChoreFamily();

    $this->actingAs($parent)
        ->post(route('parent.chores-setting.store'), [
            'chore_name' => 'お風呂掃除',
            'reward_amount' => 200,
        ])
        ->assertRedirect(route('parent.chores-setting.index'))
        ->assertSessionHas('success', 'お手伝い設定を登録しました。');

    $this->assertDatabaseHas('chores', [
        'family_id' => $family->id,
        'chore_name' => 'お風呂掃除',
        'reward_amount' => 200,
        'created_by' => $parent->id,
    ]);
});

it('updates a family chore through an ajax request', function () {
    [$parent, $family] = createChoreFamily();
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '掃除',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);

    $this->actingAs($parent)
        ->putJson(route('parent.chores-setting.update', $chore), [
            'chore_name' => 'リビング掃除',
            'reward_amount' => 250,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'お手伝い設定を更新しました。')
        ->assertJsonPath('chore.chore_name', 'リビング掃除')
        ->assertJsonPath('chore.reward_amount_label', '250円');

    $this->assertDatabaseHas('chores', [
        'id' => $chore->id,
        'chore_name' => 'リビング掃除',
        'reward_amount' => 250,
    ]);
});

it('deletes a family chore through an ajax request', function () {
    [$parent, $family] = createChoreFamily();
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '玄関掃除',
        'reward_amount' => 150,
        'created_by' => $parent->id,
    ]);

    $this->actingAs($parent)
        ->deleteJson(route('parent.chores-setting.destroy', $chore))
        ->assertOk()
        ->assertJsonPath('message', 'お手伝い設定を削除しました。');

    $this->assertSoftDeleted('chores', ['id' => $chore->id]);
});

it('returns json validation errors for ajax updates', function () {
    [$parent, $family] = createChoreFamily();
    $chore = Chore::create([
        'family_id' => $family->id,
        'chore_name' => '洗濯',
        'reward_amount' => 100,
        'created_by' => $parent->id,
    ]);

    $this->actingAs($parent)
        ->putJson(route('parent.chores-setting.update', $chore), [
            'chore_name' => '',
            'reward_amount' => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['chore_name', 'reward_amount']);
});

it('forbids editing and deleting chores from another family', function () {
    [$parent] = createChoreFamily('56565656');
    [$otherParent, $otherFamily] = createChoreFamily('78787878');
    $otherChore = Chore::create([
        'family_id' => $otherFamily->id,
        'chore_name' => '他家族のお手伝い',
        'reward_amount' => 500,
        'created_by' => $otherParent->id,
    ]);

    $this->actingAs($parent)
        ->putJson(route('parent.chores-setting.update', $otherChore), [
            'chore_name' => '不正な更新',
            'reward_amount' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($parent)
        ->deleteJson(route('parent.chores-setting.destroy', $otherChore))
        ->assertForbidden();

    $this->assertDatabaseHas('chores', [
        'id' => $otherChore->id,
        'chore_name' => '他家族のお手伝い',
        'deleted_at' => null,
    ]);
});
