<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga activa una feature de plan específica.
 *
 * Uso en rutas:
 *   ->middleware('feature:historial')
 *   ->middleware('feature:estadisticas')
 *   ->middleware('feature:reporte_clinico')
 */
class RequireFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'ok'    => false,
                'error' => 'No autenticado.',
            ], 401);
        }

        $features = $user->features();

        if (empty($features[$feature])) {
            return response()->json([
                'ok'      => false,
                'error'   => 'Tu plan actual no incluye esta función.',
                'feature' => $feature,
                'upgrade' => config('app.url') . '/planes/pro',
            ], 403);
        }

        return $next($request);
    }
}
