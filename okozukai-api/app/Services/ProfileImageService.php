<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileImageService
{
    public function update(User $user, string $name, ?UploadedFile $image): void
    {
        $oldPath = $user->profile_image;
        $newPath = $image?->store('profile-images', 'public');

        $user->update([
            'name' => $name,
            'profile_image' => $newPath ?? $oldPath,
        ]);

        if ($newPath !== null && $oldPath !== null && str_starts_with($oldPath, 'profile-images/')) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
