<?php

namespace App\Http\Controllers;

use App\Models\ProOrder;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function handle(Request $request)
    {
        // ── Verificación de firma HMAC-SHA256 ────────────────────────────────
        // MercadoPago envía x-signature y x-request-id en cada webhook.
        // Si MERCADOPAGO_WEBHOOK_SECRET está configurado en .env, verificamos.
        // Sin el secret configurado se registra una advertencia pero se continúa
        // (modo permisivo para facilitar el onboarding inicial).
        //
        // Para activar la verificación:
        //   1. En el panel MP: Configuración → Notificaciones → copia el "secret"
        //   2. Añade a .env: MERCADOPAGO_WEBHOOK_SECRET=tu_secret
        $webhookSecret = config('services.mercadopago.webhook_secret');

        if ($webhookSecret) {
            if (!$this->verifySignature($request, $webhookSecret)) {
                Log::warning('MercadoPago webhook: firma inválida', [
                    'ip'         => $request->ip(),
                    'signature'  => $request->header('x-signature'),
                    'request_id' => $request->header('x-request-id'),
                ]);
                return response()->json(['ok' => false, 'error' => 'Firma inválida'], 401);
            }
        } else {
            Log::debug('MercadoPago webhook: MERCADOPAGO_WEBHOOK_SECRET no configurado; omitiendo verificación de firma');
        }

        // ── Tipo de evento ───────────────────────────────────────────────────
        $type = $request->input('type') ?? $request->input('topic');

        if ($type !== 'payment') {
            return response()->json(['ok' => true]);
        }

        $paymentId = $request->input('data.id') ?? $request->input('id');
        if (!$paymentId) {
            return response()->json(['ok' => true]);
        }

        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        try {
            $client  = new PaymentClient();
            $payment = $client->get($paymentId);
        } catch (\Exception $e) {
            Log::error('MercadoPago webhook: no se pudo obtener pago', [
                'id'    => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['ok' => false], 500);
        }

        $orderId = $payment->external_reference;
        $order   = ProOrder::find($orderId);

        if (!$order) {
            Log::warning('MercadoPago webhook: orden no encontrada', ['ref' => $orderId]);
            return response()->json(['ok' => true]);
        }

        $order->update([
            'mp_payment_id'   => $paymentId,
            'mp_status'       => $payment->status,
            'mp_payment_type' => $payment->payment_type_id,
        ]);

        if ($payment->status === 'approved' && $order->status !== 'paid') {
            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            // Delegamos al servicio — único lugar que activa suscripciones MP.
            $this->subscriptionService->activateFromOrder($order->fresh());
        }

        return response()->json(['ok' => true]);
    }

    // ── HMAC-SHA256 ───────────────────────────────────────────────────────────
    // Formato de x-signature: "ts=1704908010,v1=618c853..."
    // Mensaje firmado:        "id:{data_id};request-id:{x-request-id};ts:{ts};"
    private function verifySignature(Request $request, string $secret): bool
    {
        $signatureHeader = $request->header('x-signature', '');
        $requestId       = $request->header('x-request-id', '');

        // Extraer ts y v1 del header
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[trim($k)] = trim($v);
        }

        $ts       = $parts['ts']  ?? '';
        $received = $parts['v1']  ?? '';

        if (!$ts || !$received) {
            return false;
        }

        $dataId  = $request->input('data.id') ?? $request->input('id', '');
        $message = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $message, $secret);

        return hash_equals($expected, $received);
    }
}
