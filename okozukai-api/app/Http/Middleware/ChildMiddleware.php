<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChildMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        // 現在ログイン中のユーザが存在していない場合
        if ($request->user() === null) {
            if ($request->routeIs('child.login', 'child.login.store')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'ログインの有効期限が切れました。'], 401);
            }

            return redirect()->guest(route('child.login'));
        }

        // 保護者ユーザがログインした場合保護者画面のお子様一覧にリダイレクト
        if ($request->user()->role !== 'child') {
            return redirect()->route('parent.children.index');
        }

        return $next($request);
    }
}
