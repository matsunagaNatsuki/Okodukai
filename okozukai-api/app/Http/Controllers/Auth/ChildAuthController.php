<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChildLoginRequest;
use App\Models\Family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChildAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.child-login');
    }

    public function login(ChildLoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $family = Family::where('family_code', $validated['family_code'])->first();
        $child = $family?->users()
            ->where('login_id', $validated['login_id'])
            ->where('role', 'child')
            ->first();

        if ($child === null || ! Auth::attempt([
            'id' => $child->id,
            'password' => $validated['password'],
            'role' => 'child',
        ])) {
            throw ValidationException::withMessages([
                'family_code' => '家族コード、ログインID、またはパスワードが正しくありません。',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('child.home'))
            ->with('success', 'ログインしました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('child.login')
            ->with('success', 'ログアウトしました。');
    }
}
