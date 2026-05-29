<?php

namespace App\Services;

use App\Mail\PlanActivatedMail;
use App\Models\Plan;
use App\Models\ProOrder;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionService
{
    public function resolvePlan(string $slug): Plan
    {
        return Plan::where('slug', $slug)->firstOrFail();
    }

    public function createSubscription(
        User $user,
        Plan $plan,
        ?string $provider = 'stripe',
        ?int $overrideDays = null
    ): Subscription {
        // billing_days = duración real del ciclo (30 mensual, 365 anual).
        // trial_days   = días de prueba gratuita (campo separado, no confundir).
        // Si el plan es gratuito no expira nunca (null).
        $billingDays = $overrideDays ?? $plan->billing_days ?? 30;
        $expiresAt   = ($plan->price_cents === 0 || $billingDays === 0)
            ? null
            : Carbon::now()->addDays($billingDays);

        // Marcar suscripciones activas previas como reemplazadas
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'replaced']);

        $subscription = Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'provider'   => $provider,
            'started_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        // Invalidar caché del plan para que el próximo request refleje el cambio
        $user->forgetPlanCache();

        return $subscription;
    }

    public function getActiveSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', Carbon::now());
            })
            ->latest('expires_at')
            ->first();
    }

    /**
     * Activa la suscripción correspondiente a un ProOrder pagado.
     *
     * Este método es la única fuente de verdad para la activación — se usa tanto
     * desde el webhook de MercadoPago como desde el polling de la app móvil
     * (checkOrder). Así se garantiza idempotencia y coherencia.
     *
     * Corrección respecto a la versión anterior: usa $order->plan_slug para
     * determinar el plan en lugar de tener hardcodeado 'pro'.
     */
    public function activateFromOrder(ProOrder $order): void
    {
        if (!$order->user_id) {
            return;
        }

        $planSlug = $order->plan_slug ?? Plan::PRO;
        $plan     = Plan::where('slug', $planSlug)->first();

        if (!$plan) {
            Log::error('SubscriptionService::activateFromOrder: plan no encontrado', [
                'order_id'  => $order->id,
                'plan_slug' => $planSlug,
            ]);
            return;
        }

        // Idempotencia: no crear duplicado si este pago ya activó una suscripción.
        $alreadyActive = Subscription::where('user_id', $order->user_id)
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->where('external_subscription_id', $order->mp_payment_id)
            ->exists();

        if ($alreadyActive) {
            return;
        }

        // Reemplazar suscripciones activas previas (cualquier plan)
        Subscription::where('user_id', $order->user_id)
            ->where('status', 'active')
            ->update(['status' => 'replaced']);

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

        // Email de confirmación al usuario (en cola para no bloquear el webhook)
        try {
            $user = $order->user;
            // Invalidar caché del plan activo
            $user?->forgetPlanCache();
            if ($user) {
                Mail::to($user->email)->queue(new PlanActivatedMail(
                    user:     $user,
                    planSlug: $plan->slug,
                    planName: $plan->name,
                    features: $plan->features ?? [],
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('PlanActivatedMail falló', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
