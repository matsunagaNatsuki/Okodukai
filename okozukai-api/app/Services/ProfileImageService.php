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
        // 先にアップロードされているプロフィール画像
        $oldPath = $user->profile_image;

        // 新しくアップロードされたファイルを、publicディスク内のprofile-imagesフォルダに保存
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
