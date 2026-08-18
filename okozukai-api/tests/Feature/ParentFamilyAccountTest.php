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
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('parent.family-account.index'))
        ->assertSessionHas('success', '保護者を追加しました。');

    $account = User::where('email', 'second-parent@example.com')->firstOrFail();
    expect($account->family_id)->toBe($family->id)
        ->and($account->role)->toBe('parent')
        ->and($account->login_id)->toBeNull()
        ->and(Hash::check('password123', $account->password))->toBeTrue();
});

it('shows role-specific login fields and profile images in the account list', function () {
    [$owner, $family] = createManagedFamily();
    $owner->update([
        'email' => 'owner@example.com',
        'login_id' => null,
        'profile_image' => 'profile-images/parent.jpg',
    ]);
    User::factory()->create([
        'family_id' => $family->id,
        'name' => '画像なしのお子様',
        'email' => null,
        'login_id' => 'child-image-test',
        'profile_image' => null,
        'role' => 'child',
    ]);

    $this->actingAs($owner)
        ->get(route('parent.family-account.index'))
        ->assertOk()
        ->assertSee('storage/profile-images/parent.jpg', false)
        ->assertSee('images/default-profile.svg', false)
        ->assertSee('メールアドレス：owner@example.com')
        ->assertSee('ログインID：child-image-test')
        ->assertSee('お子様')
        ->assertDontSee('id="account-'.$owner->id.'-login-id"', false);
});

it('accepts only passwords between 8 and 15 characters for new family accounts', function (string $routeName, array $identity, int $length, bool $valid) {
    [$owner] = createManagedFamily((string) random_int(10000000, 99999999));
    $password = str_repeat('a', $length);

    $response = $this->actingAs($owner)->post(route($routeName), array_merge([
        'name' => '文字数テスト',
        'password' => $password,
        'password_confirmation' => $password,
    ], $identity));

    if ($valid) {
        $response->assertSessionHasNoErrors()->assertRedirect(route('parent.family-account.index'));
    } else {
        $response->assertSessionHasErrors('password');
    }
})->with([
    '保護者・7文字' => ['parent.family-account.parents.store', ['email' => 'parent7@example.com'], 7, false],
    '保護者・8文字' => ['parent.family-account.parents.store', ['email' => 'parent8@example.com'], 8, true],
    '保護者・15文字' => ['parent.family-account.parents.store', ['email' => 'parent15@example.com'], 15, true],
    '保護者・16文字' => ['parent.family-account.parents.store', ['email' => 'parent16@example.com'], 16, false],
    'お子様・7文字' => ['parent.family-account.children.store', ['login_id' => 'child7'], 7, false],
    'お子様・8文字' => ['parent.family-account.children.store', ['login_id' => 'child8'], 8, true],
    'お子様・15文字' => ['parent.family-account.children.store', ['login_id' => 'child15'], 15, true],
    'お子様・16文字' => ['parent.family-account.children.store', ['login_id' => 'child16'], 16, false],
]);

it('uses email for managed parents and family code with login id for managed children', function () {
    [$owner, $family] = createManagedFamily('24681357');

    $this->actingAs($owner)->post(route('parent.family-account.parents.store'), [
        'name' => 'ログイン保護者',
        'email' => 'managed-parent@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasNoErrors();

    $this->actingAs($owner)->post(route('parent.family-account.children.store'), [
        'name' => 'ログインお子様',
        'login_id' => 'managed-child',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasNoErrors();

    auth()->logout();
    $this->post('/parent/login', [
        'email' => 'managed-parent@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('parent.children.index'));
    expect(auth()->user()->role)->toBe('parent');

    auth()->logout();
    $this->post(route('child.login.store'), [
        'family_code' => $family->family_code,
        'login_id' => 'managed-child',
        'password' => 'password123',
    ])->assertRedirect(route('child.home'));
    expect(auth()->user()->role)->toBe('child');
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

it('accepts only passwords between 8 and 15 characters when updating either role', function (string $role, int $length, bool $valid) {
    [$owner, $family] = createManagedFamily();
    $account = User::factory()->create([
        'family_id' => $family->id,
        'email' => $role === 'parent' ? "update-{$role}-{$length}@example.com" : null,
        'login_id' => $role === 'child' ? "update-{$role}-{$length}" : null,
        'role' => $role,
    ]);
    $password = str_repeat('b', $length);
    $identity = $role === 'parent'
        ? ['email' => "updated-{$role}-{$length}@example.com"]
        : ['login_id' => "updated-{$role}-{$length}"];

    $response = $this->actingAs($owner)->put(route('parent.family-account.update', $account), array_merge([
        'name' => '更新文字数テスト',
        'password' => $password,
        'password_confirmation' => $password,
    ], $identity));

    if ($valid) {
        $response->assertSessionHasNoErrors();
        expect(Hash::check($password, $account->fresh()->password))->toBeTrue();
    } else {
        $response->assertSessionHasErrors('password');
    }
})->with([
    '保護者更新・7文字' => ['parent', 7, false],
    '保護者更新・8文字' => ['parent', 8, true],
    '保護者更新・15文字' => ['parent', 15, true],
    '保護者更新・16文字' => ['parent', 16, false],
    'お子様更新・7文字' => ['child', 7, false],
    'お子様更新・8文字' => ['child', 8, true],
    'お子様更新・15文字' => ['child', 15, true],
    'お子様更新・16文字' => ['child', 16, false],
]);

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
