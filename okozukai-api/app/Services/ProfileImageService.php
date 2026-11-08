<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// プロフィール画像をアップロードするService
class ProfileImageService
{
    public function update(User $user, string $name, ?UploadedFile $image): void
    {
        // 更新前のプロフィール画像の保存先パスを取得
        $oldPath = $user->profile_image;

        // 新しくアップロードされた画像ファイルを、'storage/app/public/profile-images'に保存
        $newPath = $image?->store('profile-images', 'public');

        // プロフィール更新処理
        $user->update([
            'name' => $name,
            'profile_image' => $newPath ?? $oldPath,
        ]);

        // 前にアップロードした画像ファイルはStorageから削除する
        if ($newPath !== null && $oldPath !== null && str_starts_with($oldPath, 'profile-images/')) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
