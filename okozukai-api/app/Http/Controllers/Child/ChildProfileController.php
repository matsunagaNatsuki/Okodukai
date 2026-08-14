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
    public function __construct(private readonly ProfileImageService $profileImageService) {}

    public function edit(Request $request): View
    {
        return view('child.profile.edit', [
            'child' => $request->user(),
        ]);
    }

    public function update(UpdateChildProfileRequest $request): RedirectResponse
    {
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
