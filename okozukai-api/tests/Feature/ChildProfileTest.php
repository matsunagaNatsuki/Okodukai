<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// テストを行うたびにDBを初期化
uses(RefreshDatabase::class);

// お子様ユーザのデフォルト画像に関するテスト
it('shows the authenticated child profile with a default image', function () {
    // プロフィール画像を設定していないお子様のダミーデータ
    $child = User::factory()->create([
        'name' => '子ども花子',
        'email' => null,
        'login_id' => 'child-profile',
        'role' => 'child',
        'profile_image' => null,
    ]);

    // デフォルトの画像が表示されるか
    $this->actingAs($child)
        ->get(route('child.profile.edit'))
        ->assertOk()
        ->assertSee('子ども花子')
        ->assertSee(asset('images/default-profile.svg'));
});

// お子様のプロフィール情報の認証に関するテスト
it('updates only the authenticated child and stores a profile image', function () {
    // テスト用のストレージの作成
    Storage::fake('public');

    // プロフィール情報を変更する子供
    $child = User::factory()->create(['role' => 'child']);
    // 変更しない子供
    $otherChild = User::factory()->create([
        'role' => 'child',
        'name' => '変更されない子'
    ]);

    // お子様のプロフィール情報が正常に変更できるか？
    $this->actingAs($child)
        ->put(route('child.profile.update'), [
            'name' => '更新した子ども',
            // 500KBのダミー画像の作成
            'profile_image' => UploadedFile::fake()->image(
                'avatar.jpg', 500, 500
            )->size(500),
        ])
        ->assertRedirect(route('child.profile.edit'))
        ->assertSessionHas('success', 'プロフィールを更新しました。');

    // 更新したお子様情報の取得
    $child->refresh();

    // プロフィール情報変更後、データが一致しているか
    expect($child->name)->toBe('更新した子ども')
        ->and($child->profile_image)->toStartWith('profile-images/')
        ->and($otherChild->fresh()->name)->toBe('変更されない子');

    // ストレージに変更した画像が保存されているか
    Storage::disk('public')->assertExists($child->profile_image);
});

// 画像を変更しないときのプロフィール情報に関するテスト
it('keeps the current image when no new image is uploaded', function () {
    // テスト用のストレージを作成
    Storage::fake('public');
    // 画像ファイルの作成
    Storage::disk('public')->put('profile-images/current.jpg', 'image');

    // お子様プロフィール情報
    $child = User::factory()->create([
        'role' => 'child',
        'profile_image' => 'profile-images/current.jpg',
    ]);

    // 正常にお子様のプロフィール情報の変更ができているか
    $this->actingAs($child)
        // 名前のみ変更。プロフィール画像は変更しない
        ->put(route('child.profile.update'), ['name' => '名前だけ変更'])
        ->assertRedirect(route('child.profile.edit'));

    // 名前のみプロフィールを変更後、画像は変更されていないか
    expect($child->fresh()->profile_image)->toBe('profile-images/current.jpg');
    // ストレージにそのまま画像ファイルが入っていないか
    Storage::disk('public')->assertExists('profile-images/current.jpg');
});

// プロフィール画像変更前後のストレージデータに関するテスト
it('deletes the old managed image after replacement', function () {
    // テスト用のストレージの作成
    Storage::fake('public');
    // 画像ファイルの作成
    Storage::disk('public')->put('profile-images/old.jpg', 'old-image');

    // お子様プロフィール情報
    $child = User::factory()->create([
        'role' => 'child',
        'profile_image' => 'profile-images/old.jpg',
    ]);

    // プロフィール画像を変更
    $this->actingAs($child)->put(route('child.profile.update'), [
        'name' => $child->name,
        'profile_image' => UploadedFile::fake()
                        ->image('new.png')
                        ->size(300),
    ]);

    // ストレージ内に変更前の画像が残っていないか
    Storage::disk('public')->assertMissing('profile-images/old.jpg');

    // 更新したプロフィール画像がストレージ内に存在しているか
    Storage::disk('public')->assertExists(
        $child->fresh()->profile_image
    );
});

it('validates the child name and profile image', function () {
    // テスト用のストレージを作成
    Storage::fake('public');

    // お子様ダミーユーザ
    $child = User::factory()->create(['role' => 'child']);

    // 名前入力を空欄にしてフォーム送信後の動作確認
    $this->actingAs($child)
        ->from(route('child.profile.edit'))
        ->put(route('child.profile.update'), [
            'name' => '',
            'profile_image' => UploadedFile::fake()
            ->create(
                'document.pdf', 100,
                'application/pdf'
            ),
        ])
        ->assertRedirect(route('child.profile.edit'))
        // バリデーションが表示されるか
        ->assertSessionHasErrors(['name', 'profile_image']);

    // 2049KBの画像をアップロードした時の動作テスト
    $this->actingAs($child)
        ->from(route('child.profile.edit'))
        ->put(route('child.profile.update'), [
            'name' => $child->name,
            // 2049KBのダミー画像の作成
            'profile_image' => UploadedFile::fake()
                            ->image('large.jpg')
                            ->size(2049),
        ])
        // バリデーションエラーが表示されるか
        ->assertSessionHasErrors('profile_image');

    // プロフィール情報が更新されていないか確認
    expect($child->fresh()->profile_image)->toBeNull();
});
