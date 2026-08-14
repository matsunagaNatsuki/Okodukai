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
    public function __construct(private readonly ProfileImageService $profileImageService) {}

    public function edit(Request $request): View
    {
        return view('parent.profile.edit', [
            'parent' => $request->user(),
        ]);
    }

    public function update(UpdateParentProfileRequest $request): RedirectResponse
    {
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
