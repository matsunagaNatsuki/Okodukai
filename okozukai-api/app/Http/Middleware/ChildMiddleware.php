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
        if ($request->user() === null) {
            if ($request->routeIs('child.login', 'child.login.store')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'ログインの有効期限が切れました。'], 401);
            }

            return redirect()->guest(route('child.login'));
        }

        if ($request->user()->role !== 'child') {
            return redirect()->route('parent.children.index');
        }

        return $next($request);
    }
}
