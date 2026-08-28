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
    public function store(Request $request)
    {
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

        $code = (string) random_int(1000, 9999);

        $verification = ParentRegistrationVerification::create([
            'token' => (string) Str::uuid(),

            'name' => $validated['name'],

            'email' => $validated['email'],

            'password' => Crypt::encryptString(
                $validated['password']
            ),

            'code' => Hash::make($code),

            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($validated['email'])
            ->send(new ParentRegistrationCodeMail($code));

        return redirect()->route(
            'parent.register.verify',
            ['token' => $verification->token]
        );
    }
}
