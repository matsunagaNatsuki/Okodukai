<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SelectPasswordResetUserRequest;
use App\Http\Requests\Auth\SendPasswordResetCodeRequest;
use App\Http\Requests\Auth\UpdateResetPasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Models\PasswordResetCode;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $service) {}

    public function showRequest(Request $request): View
    {
        $request->session()->forget($this->sessionKeys());

        return view('auth.password-reset.request');
    }

    public function sendCode(SendPasswordResetCodeRequest $request): RedirectResponse
    {
        $resetCode = $this->service->issueCode($request->validated('email'));
        $request->session()->put('password_reset_owner_id', $resetCode->user_id);
        $request->session()->forget(['password_reset_code_id', 'password_reset_user_id']);

        return redirect()->route('password-reset.code')
            ->with('success', '4桁の確認コードをメールで送信しました。コードは10分間有効です。');
    }

    public function showCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_owner_id')) {
            return redirect()->route('password-reset.request');
        }

        return view('auth.password-reset.code');
    }

    public function verifyCode(VerifyPasswordResetCodeRequest $request): RedirectResponse
    {
        $ownerId = $request->session()->get('password_reset_owner_id');

        if (! is_int($ownerId)) {
            return redirect()->route('password-reset.request');
        }

        $resetCode = $this->service->verifyCode($ownerId, $request->validated('code'));
        $request->session()->put('password_reset_code_id', $resetCode->id);

        return redirect()->route('password-reset.user');
    }

    public function showUser(Request $request): View|RedirectResponse
    {
        $resetCode = $this->activeResetCode($request);

        if ($resetCode === null) {
            return $this->restart($request);
        }

        return view('auth.password-reset.user', [
            'users' => $this->service->familyUsers($resetCode),
        ]);
    }

    public function selectUser(SelectPasswordResetUserRequest $request): RedirectResponse
    {
        $resetCode = $this->activeResetCode($request);

        if ($resetCode === null) {
            return $this->restart($request);
        }

        $userId = (int) $request->validated('user_id');

        if (! $this->service->familyUsers($resetCode)->contains('id', $userId)) {
            throw ValidationException::withMessages(['user_id' => '選択したユーザーを確認できません。']);
        }

        $request->session()->put('password_reset_user_id', $userId);

        return redirect()->route('password-reset.password');
    }

    public function showPassword(Request $request): View|RedirectResponse
    {
        $resetCode = $this->activeResetCode($request);
        $userId = $request->session()->get('password_reset_user_id');

        if ($resetCode === null || ! is_int($userId)) {
            return $this->restart($request);
        }

        $target = $this->service->familyUsers($resetCode)->firstWhere('id', $userId);

        if ($target === null) {
            return $this->restart($request);
        }

        return view('auth.password-reset.password', ['target' => $target]);
    }

    public function updatePassword(UpdateResetPasswordRequest $request): RedirectResponse
    {
        $resetCode = $this->activeResetCode($request);
        $userId = $request->session()->get('password_reset_user_id');

        if ($resetCode === null || ! is_int($userId)) {
            return $this->restart($request);
        }

        $target = $this->service->resetPassword($resetCode, $userId, $request->validated('password'));
        $request->session()->forget($this->sessionKeys());

        return redirect()->route($target->role === 'child' ? 'child.login' : 'parent.login')
            ->with('success', 'パスワードを再設定しました。新しいパスワードでログインしてください。');
    }

    private function activeResetCode(Request $request): ?PasswordResetCode
    {
        $codeId = $request->session()->get('password_reset_code_id');
        $ownerId = $request->session()->get('password_reset_owner_id');

        if (! is_int($codeId) || ! is_int($ownerId)) {
            return null;
        }

        return PasswordResetCode::query()
            ->whereKey($codeId)
            ->where('user_id', $ownerId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    private function restart(Request $request): RedirectResponse
    {
        $request->session()->forget($this->sessionKeys());

        return redirect()->route('password-reset.request')
            ->with('error', '再設定手続きの有効期限が切れました。最初からやり直してください。');
    }

    private function sessionKeys(): array
    {
        return ['password_reset_owner_id', 'password_reset_code_id', 'password_reset_user_id'];
    }
}
