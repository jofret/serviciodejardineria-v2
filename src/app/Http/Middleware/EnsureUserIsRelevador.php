<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRelevador
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || $user->role !== 'relevador' || ! $user->is_active) {
            Auth::guard('web')->logout();

            return redirect()->route('relevador.login');
        }

        return $next($request);
    }
}
