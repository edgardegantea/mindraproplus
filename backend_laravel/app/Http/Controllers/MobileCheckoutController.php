<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\ProOrder;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

/**
 * Checkout de MercadoPago iniciado desde la app móvil.
 *
 * Soporta los planes PRO y PLUS:
 *   PRO  → $149 MXN/mes  · $1 430 MXN/año  (-20%)
 *   PLUS → $199 MXN/mes  · $1 910 MXN/año  (-20%)
 *
 * Flujo:
 *   1. App → POST /api/checkout/{plan_slug}   → crea ProOrder + preferencia MP
 *   2. Devuelve { ok, checkout_url, order_id }
 *   3. App abre checkout_url en el navegador del sistema
 *   4. MP redirige a /app-callback (página web que indica "regresa a la app")
 *   5. Webhook /api/webhooks/mercadopago activa la suscripción
 *   6. App llama GET /api/checkout/orders/{id} para verificar el estado
 */
class MobileCheckoutController extends Controller
{
    /** Precios en centavos por plan y periodo. */
    private const PRICES = [
        Plan::PRO  => ['monthly' => 14900, 'annual' => 143000],
        Plan::PLUS => ['monthly' => 19900, 'annual' => 191000],
    ];

    /** Nombres para el ítem de MercadoPago. */
    private const TITLES = [
        Plan::PRO  => ['monthly' => 'Mindra Pro — Mensual',  'annual' => 'Mindra Pro — Anual'],
        Plan::PLUS => ['monthly' => 'Mindra Plus — Mensual', 'annual' => 'Mindra Plus — Anual'],
    ];

    // ── Ruta principal ────────────────────────────────────────────────────────

    /**
     * POST /api/checkout/{plan_slug}
     *
     * @param  string  $planSlug  'pro' | 'plus'
     */
    public function createCheckout(Request $request, string $planSlug)
    {
        if (!array_key_exists($planSlug, self::PRICES)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Plan no válido. Usa "pro" o "plus".',
            ], 422);
        }

        $validated = $request->validate([
            'billing_period' => 'required|in:monthly,annual',
            'phone'          => 'nullable|string|max:50',
        ]);

        $user     = $request->user();
        $period   = $validated['billing_period'];

        $amountCents = self::PRICES[$planSlug][$period];
        $amount      = $amountCents / 100;
        $title       = self::TITLES[$planSlug][$period];

        // Crear orden pendiente
        $order = ProOrder::create([
            'user_id'        => $user->id,
            'full_name'      => $user->name,
            'email'          => $user->email,
            'phone'          => $validated['phone'] ?? null,
            'amount_cents'   => $amountCents,
            'currency'       => 'MXN',
            'billing_period' => $period,
            'plan_slug'      => $planSlug,
            'status'         => 'pending',
        ]);

        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            Log::error('MobileCheckout: access_token no configurado');
            return response()->json([
                'ok'      => false,
                'message' => 'Sistema de pagos no configurado. Contacta al administrador.',
            ], 503);
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        $baseCallbackUrl = config('app.url') . '/app-callback';

        $preferenceData = [
            'items' => [[
                'id'          => $planSlug . '-' . $order->id,
                'title'       => $title,
                'quantity'    => 1,
                'unit_price'  => (float) $amount,
                'currency_id' => 'MXN',
            ]],
            'payer' => [
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'back_urls' => [
                'success' => $baseCallbackUrl . '?order=' . $order->id . '&status=success',
                'failure' => $baseCallbackUrl . '?order=' . $order->id . '&status=failure',
                'pending' => $baseCallbackUrl . '?order=' . $order->id . '&status=pending',
            ],
            'auto_return'        => 'approved',
            'external_reference' => (string) $order->id,
        ];

        try {
            $client     = new PreferenceClient();
            $preference = $client->create($preferenceData);
        } catch (MPApiException $e) {
            Log::error('MobileCheckout: MercadoPago API error', [
                'status' => $e->getStatusCode(),
                'body'   => $e->getApiResponse()?->getContent() ?? 'sin detalle',
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo conectar con MercadoPago. Intenta de nuevo.',
            ], 502);
        } catch (\Throwable $e) {
            Log::error('MobileCheckout: error inesperado', ['message' => $e->getMessage()]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error al procesar el pago.',
            ], 500);
        }

        $order->update(['mp_preference_id' => $preference->id]);

        return response()->json([
            'ok'           => true,
            'checkout_url' => $preference->init_point,
            'order_id'     => $order->id,
            'amount'       => $amount,
            'currency'     => 'MXN',
            'period'       => $period,
            'plan_slug'    => $planSlug,
        ]);
    }

    // ── Alias retrocompatibilidad (ruta anterior /checkout/pro) ──────────────

    /** @deprecated Usa POST /api/checkout/{plan_slug} */
    public function createProCheckout(Request $request)
    {
        return $this->createCheckout($request, Plan::PRO);
    }

    // ── Verificación de orden ─────────────────────────────────────────────────

    /**
     * GET /api/checkout/orders/{order}
     *
     * Si la orden está pagada y la suscripción no existe todavía, la activa
     * como fallback (por si el webhook tardó o falló).
     */
    public function checkOrder(Request $request, int $orderId)
    {
        $order = ProOrder::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'paid') {
            $planSlug = $order->plan_slug ?? Plan::PRO;
            $plan     = Plan::where('slug', $planSlug)->first();

            if ($plan) {
                $activeSub = Subscription::where('user_id', $order->user_id)
                    ->where('plan_id', $plan->id)
                    ->where('status', 'active')
                    ->exists();

                if (!$activeSub) {
                    Subscription::where('user_id', $order->user_id)
                        ->where('status', 'active')
                        ->update(['status' => 'cancelled']);

                    $days = $order->billing_period === 'annual' ? 365 : 30;

                    Subscription::create([
                        'user_id'                  => $order->user_id,
                        'plan_id'                  => $plan->id,
                        'status'                   => 'active',
                        'provider'                 => 'mercadopago',
                        'external_subscription_id' => $order->mp_payment_id,
                        'started_at'               => Carbon::now(),
                        'expires_at'               => Carbon::now()->addDays($days),
                    ]);
                }
            }
        }

        return response()->json([
            'ok'        => true,
            'status'    => $order->status,
            'plan_slug' => $order->plan_slug ?? Plan::PRO,
        ]);
    }
}
