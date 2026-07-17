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

        if ($user && $user->role === 'relevador' && $user->is_active) {
            return $next($request);
        }

        // Solo cerramos la sesión si es un relevador desactivado con una
        // sesión colgada. Si el usuario logueado es de otro tipo (ej. un
        // admin que entró por error a esta URL), no lo desloguearlo — solo
        // negarle el acceso, para no tirarlo también de su propia sesión.
        if ($user && $user->role === 'relevador' && ! $user->is_active) {
            Auth::guard('web')->logout();
        }

        return redirect()->route('relevador.login');
    }
}
