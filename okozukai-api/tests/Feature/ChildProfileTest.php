<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows the authenticated child profile with a default image', function () {
    $child = User::factory()->create([
        'name' => '子ども花子',
        'email' => null,
        'login_id' => 'child-profile',
        'role' => 'child',
        'profile_image' => null,
    ]);

    $this->actingAs($child)
        ->get(route('child.profile.edit'))
        ->assertOk()
        ->assertSee('子ども花子')
        ->assertSee(asset('images/default-profile.svg'));
});

it('updates only the authenticated child and stores a profile image', function () {
    Storage::fake('public');
    $child = User::factory()->create(['role' => 'child']);
    $otherChild = User::factory()->create(['role' => 'child', 'name' => '変更されない子']);

    $this->actingAs($child)
        ->put(route('child.profile.update'), [
            'name' => '更新した子ども',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(500),
        ])
        ->assertRedirect(route('child.profile.edit'))
        ->assertSessionHas('success', 'プロフィールを更新しました。');

    $child->refresh();
    expect($child->name)->toBe('更新した子ども')
        ->and($child->profile_image)->toStartWith('profile-images/')
        ->and($otherChild->fresh()->name)->toBe('変更されない子');
    Storage::disk('public')->assertExists($child->profile_image);
});

it('keeps the current image when no new image is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-images/current.jpg', 'image');
    $child = User::factory()->create([
        'role' => 'child',
        'profile_image' => 'profile-images/current.jpg',
    ]);

    $this->actingAs($child)
        ->put(route('child.profile.update'), ['name' => '名前だけ変更'])
        ->assertRedirect(route('child.profile.edit'));

    expect($child->fresh()->profile_image)->toBe('profile-images/current.jpg');
    Storage::disk('public')->assertExists('profile-images/current.jpg');
});

it('deletes the old managed image after replacement', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-images/old.jpg', 'old-image');
    $child = User::factory()->create([
        'role' => 'child',
        'profile_image' => 'profile-images/old.jpg',
    ]);

    $this->actingAs($child)->put(route('child.profile.update'), [
        'name' => $child->name,
        'profile_image' => UploadedFile::fake()->image('new.png')->size(300),
    ]);

    Storage::disk('public')->assertMissing('profile-images/old.jpg');
    Storage::disk('public')->assertExists($child->fresh()->profile_image);
});

it('validates the child name and profile image', function () {
    Storage::fake('public');
    $child = User::factory()->create(['role' => 'child']);

    $this->actingAs($child)
        ->from(route('child.profile.edit'))
        ->put(route('child.profile.update'), [
            'name' => '',
            'profile_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('child.profile.edit'))
        ->assertSessionHasErrors(['name', 'profile_image']);

    $this->actingAs($child)
        ->from(route('child.profile.edit'))
        ->put(route('child.profile.update'), [
            'name' => $child->name,
            'profile_image' => UploadedFile::fake()->image('large.jpg')->size(2049),
        ])
        ->assertSessionHasErrors('profile_image');

    expect($child->fresh()->profile_image)->toBeNull();
});
