<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createManagedFamily(string $familyCode = '31415926'): array
{
    $owner = User::factory()->create(['role' => 'parent', 'login_id' => 'owner-'.$familyCode]);
    $family = Family::create(['owner_user_id' => $owner->id, 'family_code' => $familyCode]);
    $owner->update(['family_id' => $family->id]);

    return [$owner, $family];
}

it('lists only users from the authenticated parents family', function () {
    [$owner, $family] = createManagedFamily('31415926');
    User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'family-child',
        'role' => 'child',
        'name' => '家族の子ども',
    ]);
    [$otherOwner] = createManagedFamily('27182818');

    $this->actingAs($owner)
        ->get(route('parent.family-account.index'))
        ->assertOk()
        ->assertSee('家族の子ども')
        ->assertSee('family-child')
        ->assertSee('保護者')
        ->assertSee('子ども')
        ->assertDontSee($otherOwner->name);
});

it('adds a parent to the authenticated family', function () {
    [$owner, $family] = createManagedFamily();

    $this->actingAs($owner)
        ->post(route('parent.family-account.parents.store'), [
            'name' => '追加保護者',
            'email' => 'second-parent@example.com',
            'login_id' => 'second-parent',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('parent.family-account.index'))
        ->assertSessionHas('success', '保護者を追加しました。');

    $account = User::where('login_id', 'second-parent')->firstOrFail();
    expect($account->family_id)->toBe($family->id)
        ->and($account->role)->toBe('parent')
        ->and(Hash::check('password123', $account->password))->toBeTrue();
});

it('adds a child without an email address', function () {
    [$owner, $family] = createManagedFamily();

    $this->actingAs($owner)
        ->post(route('parent.family-account.children.store'), [
            'name' => '追加子ども',
            'login_id' => 'new-child',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('parent.family-account.index'));

    $this->assertDatabaseHas('users', [
        'family_id' => $family->id,
        'name' => '追加子ども',
        'email' => null,
        'login_id' => 'new-child',
        'role' => 'child',
    ]);
});

it('rejects a duplicate login id in the same family but allows it in another family', function () {
    [$owner, $family] = createManagedFamily('12344321');
    User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'shared-login',
        'role' => 'child',
    ]);

    $this->actingAs($owner)
        ->post(route('parent.family-account.children.store'), [
            'name' => '重複する子ども',
            'login_id' => 'shared-login',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('login_id');

    [$otherOwner, $otherFamily] = createManagedFamily('56788765');
    $this->actingAs($otherOwner)
        ->post(route('parent.family-account.children.store'), [
            'name' => '別家族の子ども',
            'login_id' => 'shared-login',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasNoErrors();

    expect(User::where('login_id', 'shared-login')->count())->toBe(2)
        ->and(User::where('family_id', $otherFamily->id)->where('login_id', 'shared-login')->exists())->toBeTrue();
});

it('updates a family account without changing its role', function () {
    [$owner, $family] = createManagedFamily();
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'before-login',
        'role' => 'child',
    ]);

    $this->actingAs($owner)
        ->put(route('parent.family-account.update', $child), [
            'name' => '更新した子ども',
            'login_id' => 'after-login',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('parent.family-account.index'));

    expect($child->fresh()->name)->toBe('更新した子ども')
        ->and($child->fresh()->login_id)->toBe('after-login')
        ->and($child->fresh()->role)->toBe('child');
});

it('deletes a removable family account through an ajax request', function () {
    [$owner, $family] = createManagedFamily();
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'delete-child',
        'role' => 'child',
    ]);

    $this->actingAs($owner)
        ->deleteJson(route('parent.family-account.destroy', $child))
        ->assertOk()
        ->assertJsonPath('message', '家族アカウントを削除しました。');

    $this->assertSoftDeleted('users', ['id' => $child->id]);
});

it('forbids deleting the logged in user and the family owner', function () {
    [$owner, $family] = createManagedFamily();
    $secondParent = User::factory()->create([
        'family_id' => $family->id,
        'login_id' => 'second-parent-delete-test',
        'role' => 'parent',
    ]);

    $this->actingAs($owner)
        ->deleteJson(route('parent.family-account.destroy', $owner))
        ->assertForbidden();

    $this->actingAs($secondParent)
        ->deleteJson(route('parent.family-account.destroy', $owner))
        ->assertForbidden();

    $this->actingAs($secondParent)
        ->deleteJson(route('parent.family-account.destroy', $secondParent))
        ->assertForbidden();

    expect(User::whereKey($owner->id)->exists())->toBeTrue()
        ->and(User::whereKey($secondParent->id)->exists())->toBeTrue();
});

it('forbids editing and deleting an account from another family', function () {
    [$owner] = createManagedFamily('90909090');
    [$otherOwner] = createManagedFamily('80808080');

    $this->actingAs($owner)
        ->put(route('parent.family-account.update', $otherOwner), [
            'name' => '不正更新',
            'email' => 'invalid-update@example.com',
            'login_id' => 'invalid-update',
        ])
        ->assertForbidden();

    $this->actingAs($owner)
        ->deleteJson(route('parent.family-account.destroy', $otherOwner))
        ->assertForbidden();
});
