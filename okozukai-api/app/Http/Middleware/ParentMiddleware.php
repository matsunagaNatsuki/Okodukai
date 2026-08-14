<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParentMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($request->user() === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ログインの有効期限が切れました。'], 401);
            }

            return redirect()->guest(route('login'));
        }

        if ($request->user()->role !== 'parent') {
            return redirect()->route('child.home');
        }

        return $next($request);
    }
}
