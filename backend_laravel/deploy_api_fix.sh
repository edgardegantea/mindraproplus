#!/bin/bash
# ============================================================
#  deploy_api_fix.sh
#  Sube a: /var/www/vhosts/cafined.org/mindra.cafined.org/
#  Ejecuta: bash deploy_api_fix.sh
# ============================================================

set -e

HTTPDOCS="/var/www/vhosts/cafined.org/mindra.cafined.org"

echo ""
echo "══════════════════════════════════════════"
echo "  Mindra — Fix API (CSRF + Sanctum tokens)"
echo "══════════════════════════════════════════"
echo ""

cd "$HTTPDOCS"

# ── 1. routes/api.php ─────────────────────────────────────────────────────────
echo "[1/4] Actualizando routes/api.php ..."

cat > routes/api.php << 'PHP'
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InferenceController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Rutas públicas (sin CSRF, sin auth) ──────────────────────────────────────

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago');

// Auth — devuelven token Sanctum, no necesitan CSRF
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Planes — lectura pública para mostrarlos antes del login
Route::get('/plans',        [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);

// Inferencia — pública con throttle (usuario identificado por token si lo tiene)
Route::middleware('throttle:30,1')
    ->post('/inference/predict', [InferenceController::class, 'predict']);

// ── Rutas protegidas (requieren token Sanctum) ────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/subscriptions/current', [SubscriptionController::class, 'current']);
    Route::post('/subscriptions',        [SubscriptionController::class, 'subscribe']);

    Route::get('/inference/history', [InferenceController::class, 'history']);
    Route::get('/inference/stats',   [InferenceController::class, 'stats'])
        ->middleware('can:viewAny,App\\Models\\InferenceRecord');

    Route::middleware('can:manage-plans')->group(function () {
        Route::post('/plans',          [PlanController::class, 'store']);
        Route::put('/plans/{plan}',    [PlanController::class, 'update']);
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);
    });
});
PHP

echo "    ✓ routes/api.php listo"

# ── 2. AuthController.php ─────────────────────────────────────────────────────
echo "[2/4] Actualizando AuthController.php ..."

cat > app/Http/Controllers/AuthController.php << 'PHP'
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok'    => true,
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        $request->session()->regenerate();

        $token = Auth::user()->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok'    => true,
            'user'  => Auth::user(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'ok'      => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'ok'   => true,
            'user' => $request->user(),
        ]);
    }
}
PHP

echo "    ✓ AuthController.php listo"

# ── 3. config/cors.php ───────────────────────────────────────────────────────
echo "[3/4] Actualizando config/cors.php ..."

cat > config/cors.php << 'PHP'
<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://mindraback.cafined.org',
        'https://mindra.cafined.org',
        'https://cafined.org',
    ],

    // Permite cualquier puerto de localhost (Flutter web, Vite, React, etc.)
    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
PHP

echo "    ✓ cors.php listo"

# ── 4. Limpiar caché de Laravel ───────────────────────────────────────────────
echo "[4/4] Limpiando caché de rutas y configuración ..."

php artisan route:clear  2>&1 | sed 's/^/    /'
php artisan config:clear 2>&1 | sed 's/^/    /'

echo "    ✓ Caché limpiado"

# ── Verificación rápida ───────────────────────────────────────────────────────
echo ""
echo "Verificando endpoint /api/auth/login ..."
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
  -X POST https://mindra.cafined.org/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"wrongpass"}')

if [ "$RESPONSE" = "401" ]; then
    echo "    ✓ Respuesta 401 — CSRF eliminado, endpoint funciona correctamente"
elif [ "$RESPONSE" = "200" ]; then
    echo "    ✓ Respuesta 200 — Login exitoso"
else
    echo "    ⚠ Respuesta inesperada: HTTP $RESPONSE"
    echo "    Revisa manualmente: curl -s -X POST https://mindra.cafined.org/api/auth/login -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{\"email\":\"a@b.com\",\"password\":\"x\"}'"
fi

echo ""
echo "══════════════════════════════════════════"
echo "  ✅ Deploy completado"
echo "══════════════════════════════════════════"
echo ""
echo "  Próximo paso: reinicia la app Flutter con 'flutter run'"
echo ""
