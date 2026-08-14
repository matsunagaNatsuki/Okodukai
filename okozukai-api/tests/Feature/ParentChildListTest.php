<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createParentWithFamily(string $familyCode): array
{
    $parent = User::factory()->create(['role' => 'parent']);
    $family = Family::create([
        'owner_user_id' => $parent->id,
        'family_code' => $familyCode,
    ]);
    $parent->update(['family_id' => $family->id]);

    return [$parent, $family];
}

it('shows only children in the authenticated parents family', function () {
    [$parent, $family] = createParentWithFamily('11111111');
    [, $otherFamily] = createParentWithFamily('22222222');

    $ownChild = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'own-child',
        'role' => 'child',
        'name' => '自分の子ども',
    ]);
    User::factory()->create([
        'family_id' => $otherFamily->id,
        'email' => null,
        'login_id' => 'other-child',
        'role' => 'child',
        'name' => '別家族の子ども',
    ]);

    $this->actingAs($parent)
        ->get('/parent/children')
        ->assertOk()
        ->assertSee('自分の子ども')
        ->assertSee('own-child')
        ->assertSee(route('parent.children.show', $ownChild))
        ->assertDontSee('別家族の子ども')
        ->assertDontSee('other-child');
});

it('does not include parent users in the child list', function () {
    [$parent, $family] = createParentWithFamily('33333333');
    User::factory()->create([
        'family_id' => $family->id,
        'role' => 'parent',
        'name' => '二人目の保護者',
    ]);

    $this->actingAs($parent)
        ->get('/parent/children')
        ->assertOk()
        ->assertDontSee('二人目の保護者');
});

it('shows guidance when the family has no children', function () {
    [$parent] = createParentWithFamily('44444444');

    $this->actingAs($parent)
        ->get('/parent/children')
        ->assertOk()
        ->assertSee('家族アカウントからお子様を追加してください');
});

it('allows a parent to open a child in the same family', function () {
    [$parent, $family] = createParentWithFamily('55555555');
    $child = User::factory()->create([
        'family_id' => $family->id,
        'email' => null,
        'login_id' => 'same-family-child',
        'role' => 'child',
    ]);

    $this->actingAs($parent)
        ->get(route('parent.children.show', $child))
        ->assertOk();
});

it('forbids direct access to a child in another family', function () {
    [$parent] = createParentWithFamily('66666666');
    [, $otherFamily] = createParentWithFamily('77777777');
    $otherChild = User::factory()->create([
        'family_id' => $otherFamily->id,
        'email' => null,
        'login_id' => 'forbidden-child',
        'role' => 'child',
    ]);

    $this->actingAs($parent)
        ->get(route('parent.children.show', $otherChild))
        ->assertForbidden();
});
