<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege /api/posts (consumido por altoparque.com, el panel central, para
 * poblar el Select de post_id en su SurveyResource). No es Sanctum: un solo
 * cliente confiable (el central), token compartido simple vía env — mismo
 * criterio que WHATSAPP_CLOUD_API_VERIFY_TOKEN.
 */
class EnsureCentralApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.central_api.token');

        if (blank($token) || ! hash_equals($token, (string) $request->bearerToken())) {
            abort(401, 'Token inválido o faltante.');
        }

        return $next($request);
    }
}
