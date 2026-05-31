<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InferenceController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\MobileCheckoutController;
use App\Http\Controllers\MoodJournalController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TherapistShareController;
use App\Http\Controllers\WellnessController;
use Illuminate\Support\Facades\Route;

// ── Health check ─────────────────────────────────────────────────────────────
Route::get('/health', function () {
    $mlHealth = app(\App\Services\AI\MindrabackClient::class)->health();

    // DB connectivity
    $dbOk  = false;
    $dbErr = null;
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbErr = $e->getMessage();
    }

    // Session driver (database driver requiere tabla 'sessions' inexistente)
    $sessionDriver = config('session.driver', 'unknown');

    // Cache driver / store
    $cacheOk  = false;
    $cacheErr = null;
    try {
        \Illuminate\Support\Facades\Cache::put('_health_check', 1, 5);
        $cacheOk = \Illuminate\Support\Facades\Cache::get('_health_check') === 1;
        \Illuminate\Support\Facades\Cache::forget('_health_check');
    } catch (\Throwable $e) {
        $cacheErr = $e->getMessage();
    }

    // Tablas críticas
    $tables = [];
    foreach (['users','plans','subscriptions','inference_records','visitor_sessions',
              'notification_preferences','crisis_events'] as $tbl) {
        try {
            $tables[$tbl] = \Illuminate\Support\Facades\Schema::hasTable($tbl) ? 'ok' : 'missing';
        } catch (\Throwable) {
            $tables[$tbl] = 'error';
        }
    }

    $allOk = $dbOk && $cacheOk && ($mlHealth['reachable'] ?? false);

    return response()->json([
        'status'         => $allOk ? 'healthy' : 'degraded',
        'service'        => config('app.name'),
        'version'        => '2.1.0',
        'environment'    => app()->environment(),
        'database'       => $dbOk  ? 'ok'   : ['error' => $dbErr],
        'cache'          => $cacheOk ? 'ok' : ['error' => $cacheErr, 'store' => config('cache.default')],
        'session_driver' => $sessionDriver,
        'tables'         => $tables,
        'ml_service'     => $mlHealth,
    ], $allOk ? 200 : 503);
});

// ── Webhooks ─────────────────────────────────────────────────────────────────
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago');

// ── Auth pública (throttle estricto para mitigar fuerza bruta) ───────────────
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/register',       [AuthController::class, 'register']);
    Route::post('/auth/login',          [AuthController::class, 'login']);
    Route::post('/auth/forgot-password',[PasswordResetController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword']);
});

// ── Planes: lectura pública ───────────────────────────────────────────────────
Route::get('/plans',        [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);

// ── Inferencia: throttle inteligente por plan ─────────────────────────────────
// Free: 20/hora · Pro: 100/hora · Plus: ilimitado · Anónimo: 10/hora
Route::middleware('throttle:predict')->group(function () {
    Route::post('/inference/predict',    [InferenceController::class, 'predict']);
    Route::post('/inference/transcribe', [InferenceController::class, 'transcribe']);
});

// ── Solicitud plan Plus (puede ser anónima o autenticada) ─────────────────────
Route::post('/plus/request', [InferenceController::class, 'plusRequest']);

// ── Protegidas: requieren token Sanctum ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout',  [AuthController::class, 'logout']);
    Route::get('/auth/me',       [AuthController::class, 'me']);

    // Device tokens para push notifications (FCM)
    Route::post('/auth/device-token',   [DeviceTokenController::class, 'register']);
    Route::delete('/auth/device-token', [DeviceTokenController::class, 'unregister']);

    // Perfil de usuario
    Route::patch('/auth/profile',          [ProfileController::class, 'update']);
    Route::delete('/auth/account',         [ProfileController::class, 'deleteAccount']);
    Route::get('/auth/my-data',            [ProfileController::class, 'myData']);

    // Suscripciones
    Route::get('/subscriptions/current',   [SubscriptionController::class, 'current']);
    Route::post('/subscriptions',          [SubscriptionController::class, 'subscribe']);

    // Historial de chat agrupado por sesión
    Route::get('/chat/history',            [InferenceController::class, 'chatHistory'])
         ->middleware('feature:historial');

    // Inferencias con feature-gate
    Route::get('/inference/history',       [InferenceController::class, 'history'])
         ->middleware('feature:historial');
    Route::get('/inference/calendar',      [InferenceController::class, 'calendar'])
         ->middleware('feature:historial');
    Route::get('/inference/trends',        [InferenceController::class, 'trends'])
         ->middleware('feature:estadisticas');
    Route::get('/inference/weekly-report', [InferenceController::class, 'weeklyReport'])
         ->middleware('feature:historial');
    Route::get('/inference/clinical-report',[InferenceController::class, 'clinicalReport'])
         ->middleware('feature:reporte_clinico');
    Route::get('/inference/export',        [ExportController::class, 'inferenceHistory'])
         ->middleware('feature:historial');
    Route::get('/inference/stats',         [InferenceController::class, 'stats'])
         ->middleware('can:viewAny,App\\Models\\InferenceRecord');

    // ── Diario emocional ──────────────────────────────────────────────────────
    Route::get('/journal/today',        [MoodJournalController::class, 'today']);
    Route::get('/journal/stats',        [MoodJournalController::class, 'stats']);
    Route::get('/journal',              [MoodJournalController::class, 'index']);
    Route::post('/journal',             [MoodJournalController::class, 'store']);
    Route::delete('/journal/{id}',      [MoodJournalController::class, 'destroy']);
    Route::get('/journal/export',       [ExportController::class, 'moodJournal']);

    // ── Evaluaciones clínicas ─────────────────────────────────────────────────
    Route::get('/assessments/trends',   [AssessmentController::class, 'trends']);
    Route::get('/assessments/latest',   [AssessmentController::class, 'latest']);
    Route::get('/assessments',          [AssessmentController::class, 'index']);
    Route::post('/assessments',         [AssessmentController::class, 'store']);

    // ── Bienestar ─────────────────────────────────────────────────────────────
    Route::get('/wellness/score',       [WellnessController::class, 'score']);

    // ── Preferencias de notificación ──────────────────────────────────────────
    Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
    Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);

    // ── Racha de actividad ────────────────────────────────────────────────────
    Route::get('/streak',               [StreakController::class, 'index']);

    // ── Enlace para terapeuta ─────────────────────────────────────────────────
    Route::post('/share/therapist',     [TherapistShareController::class, 'generate']);

    // ── Checkout MercadoPago (Pro + Plus) ─────────────────────────────────────
    Route::post('/checkout/{plan_slug}',   [MobileCheckoutController::class, 'createCheckout'])
         ->where('plan_slug', 'pro|plus');
    Route::get('/checkout/orders/{order}', [MobileCheckoutController::class, 'checkOrder']);

    // ── Programas estructurados ───────────────────────────────────────────────
    Route::get('/programs',                      [ProgramController::class, 'index']);
    Route::post('/programs/{slug}/enroll',       [ProgramController::class, 'enroll']);
    Route::post('/programs/{slug}/complete-day', [ProgramController::class, 'completeDay']);

    // ── Administración de planes (solo admins) ────────────────────────────────
    Route::middleware('can:manage-plans')->group(function () {
        Route::post('/plans',          [PlanController::class, 'store']);
        Route::put('/plans/{plan}',    [PlanController::class, 'update']);
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);
    });
});
