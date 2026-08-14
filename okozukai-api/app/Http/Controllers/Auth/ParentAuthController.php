<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ParentLoginRequest;
use App\Http\Requests\Auth\ParentRegisterRequest;
use App\Services\ParentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParentAuthController extends Controller
{
    public function __construct(private readonly ParentRegistrationService $registrationService) {}

    public function showRegister(): View
    {
        return view('auth.parent-register');
    }

    public function register(ParentRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$parent, $familyCode] = $this->registrationService->register($validated);

        Auth::login($parent);
        $request->session()->regenerate();

        return redirect()
            ->route('parent.children.index')
            ->with('success', "保護者登録が完了しました。家族コードは{$familyCode}です。");
    }

    public function showLogin(Request $request): View
    {
        return view('auth.parent-login', [
            'savedEmail' => $request->cookie('saved_parent_email'),
        ]);
    }

    public function login(ParentLoginRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'parent',
        ];

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。保護者用アカウントをご確認ください。',
            ]);
        }

        $request->session()->regenerate();

        if ($request->boolean('save_email')) {
            Cookie::queue('saved_parent_email', $credentials['email'], 60 * 24 * 30);
        } else {
            Cookie::queue(Cookie::forget('saved_parent_email'));
        }

        return redirect()
            ->intended(route('parent.children.index'))
            ->with('success', 'ログインしました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('parent.login')
            ->with('success', 'ログアウトしました。');
    }
}
