<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\ProOrder;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
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
            $client = new PaymentClient();
            $payment = $client->get($paymentId);
        } catch (\Exception $e) {
            Log::error('MercadoPago webhook: no se pudo obtener pago', ['id' => $paymentId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false], 500);
        }

        $orderId = $payment->external_reference;
        $order = ProOrder::find($orderId);

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
            $this->activateProSubscription($order);
        }

        return response()->json(['ok' => true]);
    }

    private function activateProSubscription(ProOrder $order): void
    {
        if (!$order->user_id) {
            return;
        }

        $plan = Plan::where('slug', 'pro')->first();
        if (!$plan) {
            return;
        }

        $existing = Subscription::where('user_id', $order->user_id)
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->where('external_subscription_id', $order->mp_payment_id)
            ->exists();

        if ($existing) {
            return;
        }

        Subscription::where('user_id', $order->user_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $days = $order->billing_period === 'annual' ? 365 : 30;

        Subscription::create([
            'user_id'    => $order->user_id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'provider'   => 'mercadopago',
            'external_subscription_id' => $order->mp_payment_id,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays($days),
        ]);
    }
}
