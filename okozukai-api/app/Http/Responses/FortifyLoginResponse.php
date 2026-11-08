<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // もし、JSON形式のレスポンスを要求していたら
        if ($request->wantsJson()) {
            // 二要素認証のJsonは不要
            return response()->json(['two_factor' => false]);
        }

        // お子様一覧画面にてリダイレクト
        return redirect()
            ->intended(route('parent.children.index'))
            ->with('success', 'ログインしました。');
    }
}
