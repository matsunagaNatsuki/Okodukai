<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\UpdateParentProfileRequest;
use App\Services\ProfileImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentProfileController extends Controller
{
    // プロフィール画像をアップロードするService
    public function __construct(private readonly ProfileImageService $profileImageService) {}

    public function edit(Request $request): View
    {
        return view('parent.profile.edit', [
            // 保護者の名前
            'parent' => $request->user(),
        ]);
    }

    public function update(UpdateParentProfileRequest $request): RedirectResponse
    {
        // プロフィール名と画像をProfileImageServiceを使用して更新処理
        $this->profileImageService->update(
            $request->user(),
            $request->validated('name'),
            $request->file('profile_image'),
        );

        return redirect()
            ->route('parent.profile.edit')
            ->with('success', 'プロフィールを更新しました。');
    }
}
