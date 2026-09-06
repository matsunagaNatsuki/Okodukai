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
    // 確認コード入力画面
    public function create(string $token)
    {
        $verification = ParentRegistrationVerification::where('token', $token)
            ->firstOrFail();

        // 確認コード入力フォーム
        return view('auth.register-verify', [
            'token' => $verification->token,
            'email' => $verification->email,
        ]);
    }

    public function store(Request $request,string $token,CreateNewUser $createNewUser)
    {
        // 確認コードのバリデーション
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

        // 仮登録が10分以上経過すると有効期限切れのエラー
        if ($verification->expires_at->isPast()) {
            return back()->withErrors([
                'code' => '確認コードの有効期限が切れています。',
            ]);
        }

        // 入力回数を5回以上超えるとエラー
        if ($verification->attempts >= 5) {
            return back()->withErrors([
                'code' => '確認コードの入力回数が上限に達しました。再度登録手続きを行ってください。',
            ]);
        }

        if (! Hash::check($request->input('code'), $verification->code)) {
            // 確認コードが一致しなかったときはattemptsカラムをカウント
            $verification->increment('attempts');

            // 確認コードが一致しない場合のエラー
            return back()->withErrors([
                'code' => '確認コードが正しくありません。',
            ]);
        }

        // 暗号化したパスワードを元の文字列に直す
        $password = Crypt::decryptString(
            $verification->password
        );

        // 保護者アカウントの作成
        $parent = $createNewUser->create([
            'name' => $verification->name,
            'email' => $verification->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        // parent_registration_verificationsテーブルの対象データを削除
        $verification->delete();

        // 作成した保護者アカウントで保護者画面にログイン
        Auth::login($parent);

        // お子様一覧画面にリダイレクト
        return redirect()->route('parent.children.index');
    }
}
