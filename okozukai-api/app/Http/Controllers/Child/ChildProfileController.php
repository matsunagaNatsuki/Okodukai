<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\UpdateChildProfileRequest;
use App\Services\ProfileImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildProfileController extends Controller
{
    // プロフィール画像をアップロードするService
    public function __construct(private readonly ProfileImageService $profileImageService) {}

    // お子様プロフィール画面
    public function edit(Request $request): View
    {
        return view('child.profile.edit', [
            'child' => $request->user(),
        ]);
    }

    // プロフィール更新処理
    public function update(UpdateChildProfileRequest $request): RedirectResponse
    {
        // ProfileImageServiceで名前と画像をアップロード処理を行う
        $this->profileImageService->update(
            $request->user(),
            $request->validated('name'),
            $request->file('profile_image'),
        );

        return redirect()
            ->route('child.profile.edit')
            ->with('success', 'プロフィールを更新しました。');
    }
}
