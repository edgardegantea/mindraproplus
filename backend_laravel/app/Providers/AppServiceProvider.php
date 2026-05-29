<?php

namespace App\Providers;

use App\Models\InferenceRecord;
use App\Observers\InferenceRecordObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        InferenceRecord::observe(InferenceRecordObserver::class);
        $this->configureRateLimiting();
    }

    /**
     * Rate limiting por plan:
     *  - Plus:          sin límite
     *  - Pro:           100 peticiones / hora
     *  - Free auth:      20 peticiones / hora
     *  - Anónimo:        10 peticiones / hora
     *
     * La respuesta 429 incluye el header Retry-After con los segundos restantes.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('predict', function (Request $request) {
            $user = $request->user();

            if (!$user) {
                return Limit::perHour(10)->by('guest|' . $request->ip())
                    ->response(fn() => response()->json([
                        'ok'      => false,
                        'error'   => 'Límite de solicitudes alcanzado. Crea una cuenta para obtener más acceso.',
                        'upgrade' => true,
                    ], 429));
            }

            $plan = $user->activePlan();
            $slug = $plan?->slug ?? 'free';

            // Plus: sin límite
            if ($slug === 'plus') {
                return Limit::none();
            }

            // Pro: 100/hora
            if ($slug === 'pro') {
                return Limit::perHour(100)->by("pro|{$user->id}")
                    ->response(fn() => response()->json([
                        'ok'      => false,
                        'error'   => 'Has alcanzado el límite de 100 análisis por hora del plan Pro. Actualiza a Plus para análisis ilimitados.',
                        'upgrade' => true,
                    ], 429));
            }

            // Free: 20/hora
            return Limit::perHour(20)->by("free|{$user->id}")
                ->response(fn() => response()->json([
                    'ok'      => false,
                    'error'   => 'Has alcanzado el límite de 20 análisis por hora del plan Free. Actualiza a Pro o Plus para continuar.',
                    'upgrade' => true,
                ], 429));
        });
    }
}
