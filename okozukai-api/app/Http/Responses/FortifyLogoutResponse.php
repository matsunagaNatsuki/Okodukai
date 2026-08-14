<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class FortifyLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()
            ->route('login')
            ->with('success', 'ログアウトしました。');
    }
}
