<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Models\ParentRegistrationVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class ParentRegistrationVerificationController extends Controller
{
    public function create(string $token)
    {
        $verification = ParentRegistrationVerification::where('token', $token)
            ->firstOrFail();

        return view('auth.register-verify', [
            'token' => $verification->token,
            'email' => $verification->email,
        ]);
    }

    public function store(Request $request,string $token,CreateNewUser $createNewUser)
    {
        $request->validate([
            'code' => [
                'required',
                'digits:4',
            ],
        ], [
            'code.required' => '確認コードを入力してください。',
            'code.digits' => '確認コードは4桁の数字で入力してください。',
        ]);

        $verification = ParentRegistrationVerification::where('token', $token)
            ->firstOrFail();

        if ($verification->expires_at->isPast()) {
            return back()->withErrors([
                'code' => '確認コードの有効期限が切れています。',
            ]);
        }

        if ($verification->attempts >= 5) {
            return back()->withErrors([
                'code' => '確認コードの入力回数が上限に達しました。再度登録手続きを行ってください。',
            ]);
        }

        if (! Hash::check($request->input('code'), $verification->code)) {
            $verification->increment('attempts');

            return back()->withErrors([
                'code' => '確認コードが正しくありません。',
            ]);
        }

        $password = Crypt::decryptString(
            $verification->password
        );

        $parent = $createNewUser->create([
            'name' => $verification->name,
            'email' => $verification->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $verification->delete();

        Auth::login($parent);

        return redirect()->route('parent.children.index');
    }
}
