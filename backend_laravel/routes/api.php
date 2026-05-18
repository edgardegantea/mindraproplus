<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InferenceController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\MobileCheckoutController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Públicas sin CSRF ────────────────────────────────────────────────────────
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago');

// Auth: sin CSRF (peticiones desde app móvil con token Sanctum)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Planes: lectura pública
Route::get('/plans',        [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);

// Inferencia: pública con throttle (usuario identificado por token si existe)
Route::middleware('throttle:30,1')
    ->post('/inference/predict', [InferenceController::class, 'predict']);

// ── Protegidas: requieren token Sanctum ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/subscriptions/current', [SubscriptionController::class, 'current']);
    Route::post('/subscriptions',        [SubscriptionController::class, 'subscribe']);

    Route::get('/inference/history', [InferenceController::class, 'history']);
    Route::get('/inference/stats',   [InferenceController::class, 'stats'])
        ->middleware('can:viewAny,App\\Models\\InferenceRecord');

    // Checkout móvil (MercadoPago) — soporta 'pro' y 'plus'
    Route::post('/checkout/{plan_slug}',   [MobileCheckoutController::class, 'createCheckout'])
         ->where('plan_slug', 'pro|plus');
    Route::get('/checkout/orders/{order}', [MobileCheckoutController::class, 'checkOrder']);

    // Administración de planes (solo admins)
    Route::middleware('can:manage-plans')->group(function () {
        Route::post('/plans',         [PlanController::class, 'store']);
        Route::put('/plans/{plan}',   [PlanController::class, 'update']);
        Route::delete('/plans/{plan}',[PlanController::class, 'destroy']);
    });
});
