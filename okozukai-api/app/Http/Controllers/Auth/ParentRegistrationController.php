<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ParentRegistrationCodeMail;
use App\Models\ParentRegistrationVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class ParentRegistrationController extends Controller
{
    // 保護者新規登録画面
    // public function create()
    // {
    //     return view('auth.parent-register');
    // }

    // 保護者新規登録機能の処理
    public function store(Request $request)
    {
        // 保護者新規登録画面のバリデーション
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => '名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスはすでに使用されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワード確認が一致していません。',
        ]);

        // 4桁の確認コードの作成
        $code = (string) random_int(1000, 9999);

        // 保護者ユーザと確認コード情報をDBに作成
        $verification = ParentRegistrationVerification::create([
            'token' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Crypt::encryptString( // パスワードの暗号化
                $validated['password']
            ),
            'code' => Hash::make($code), // 確認コードの暗号化
            // 'expires_at' => now()->addMinutes(10), 確認コードの作成時間
        ]);

        // 確認コードメールの送信内容
        Mail::to($validated['email'])
            ->send(new ParentRegistrationCodeMail($code));

        // 確認コード入力画面にリダイレクト
        return redirect()->route(
            'parent.register.verify',
            ['token' => $verification->token]
        );
    }
}
