<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows the authenticated parent profile with a default image', function () {
    $parent = User::factory()->create([
        'name' => '保護者花子',
        'email' => 'hanako@example.com',
        'role' => 'parent',
        'profile_image' => null,
    ]);

    $this->actingAs($parent)
        ->get(route('parent.profile.edit'))
        ->assertOk()
        ->assertSee('保護者花子')
        ->assertSee('hanako@example.com')
        ->assertSee(asset('images/default-profile.svg'));
});

it('updates the parent name and stores a profile image on the public disk', function () {
    Storage::fake('public');
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->put(route('parent.profile.update'), [
            'name' => '更新した保護者',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(500),
        ])
        ->assertRedirect(route('parent.profile.edit'))
        ->assertSessionHas('success', 'プロフィールを更新しました。');

    $parent->refresh();
    expect($parent->name)->toBe('更新した保護者')
        ->and($parent->profile_image)->toStartWith('profile-images/');
    Storage::disk('public')->assertExists($parent->profile_image);
});

it('keeps the current image when no new image is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-images/current.jpg', 'image');
    $parent = User::factory()->create([
        'role' => 'parent',
        'profile_image' => 'profile-images/current.jpg',
    ]);

    $this->actingAs($parent)
        ->put(route('parent.profile.update'), ['name' => '名前だけ変更'])
        ->assertRedirect(route('parent.profile.edit'));

    expect($parent->fresh()->profile_image)->toBe('profile-images/current.jpg');
    Storage::disk('public')->assertExists('profile-images/current.jpg');
});

it('deletes the old managed image after a replacement succeeds', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-images/old.jpg', 'old-image');
    $parent = User::factory()->create([
        'role' => 'parent',
        'profile_image' => 'profile-images/old.jpg',
    ]);

    $this->actingAs($parent)->put(route('parent.profile.update'), [
        'name' => $parent->name,
        'profile_image' => UploadedFile::fake()->image('new.png')->size(300),
    ]);

    Storage::disk('public')->assertMissing('profile-images/old.jpg');
    Storage::disk('public')->assertExists($parent->fresh()->profile_image);
});

it('validates profile image type and size', function () {
    Storage::fake('public');
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->from(route('parent.profile.edit'))
        ->put(route('parent.profile.update'), [
            'name' => $parent->name,
            'profile_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('parent.profile.edit'))
        ->assertSessionHasErrors('profile_image');

    $this->actingAs($parent)
        ->from(route('parent.profile.edit'))
        ->put(route('parent.profile.update'), [
            'name' => $parent->name,
            'profile_image' => UploadedFile::fake()->image('large.jpg')->size(2049),
        ])
        ->assertSessionHasErrors('profile_image');

    expect($parent->fresh()->profile_image)->toBeNull();
});
