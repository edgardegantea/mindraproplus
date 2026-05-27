<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PlanActivatedMail;
use App\Mail\PlusRequestMail;
use App\Models\Plan;
use App\Models\ProOrder;
use App\Models\Subscription;
use App\Support\PlusRequestHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function miPlan()
    {
        $user         = auth()->user();
        $subscription = $user->activeSubscription();
        $plan         = $subscription?->plan ?? \App\Models\Plan::where('slug', 'free')->first();
        $features     = $user->features();

        return view('mi-plan', compact('user', 'subscription', 'plan', 'features'));
    }

    public function fullPlan()
    {
        return view('plans.full');
    }

    public function fullPlanSubmit(Request $request)
    {
        $validated = $request->validate([
            'institution_name'  => 'required|string|max:255',
            'institution_type'  => 'required|string|max:100',
            'contact_name'      => 'required|string|max:255',
            'contact_email'     => 'required|email|max:255',
            'contact_phone'     => 'nullable|string|max:50',
            'user_count'        => 'required|string|max:50',
            'features'          => 'nullable|string|max:2000',
            'comments'          => 'nullable|string|max:2000',
        ]);

        return redirect()->route('plans.full')->with('success', '¡Solicitud enviada correctamente! Nos pondremos en contacto contigo pronto.');
    }

    public function proPlan()
    {
        return view('plans.pro');
    }

    public function proPlanSubmit(Request $request)
    {
        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'billing_period' => 'required|in:monthly,annual',
            'accept_terms'   => 'required|accepted',
        ]);

        $isAnnual = $validated['billing_period'] === 'annual';
        $amountCents = $isAnnual ? 143000 : 14900;
        $amount = $amountCents / 100;
        $title = $isAnnual ? 'Mindra Pro — Suscripción Anual' : 'Mindra Pro — Suscripción Mensual';

        $order = ProOrder::create([
            'user_id'        => Auth::id(),
            'full_name'      => $validated['full_name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'],
            'amount_cents'   => $amountCents,
            'currency'       => 'MXN',
            'billing_period' => $validated['billing_period'],
            'plan_slug'      => 'pro',
            'status'         => 'pending',
        ]);

        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            Log::error('MercadoPago: access_token no configurado');
            return back()->with('error', 'Error de configuración del sistema de pagos. Contacta al administrador.');
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        $client = new PreferenceClient();

        $preferenceData = [
            'items' => [
                [
                    'id'          => 'pro-' . $order->id,
                    'title'       => $title,
                    'quantity'    => 1,
                    'unit_price'  => (float) $amount,
                    'currency_id' => 'MXN',
                ],
            ],
            'payer' => [
                'name'  => $validated['full_name'],
                'email' => $validated['email'],
            ],
            'back_urls' => [
                'success' => route('plans.pro.callback', ['order' => $order->id, 'status' => 'success']),
                'failure' => route('plans.pro.callback', ['order' => $order->id, 'status' => 'failure']),
                'pending' => route('plans.pro.callback', ['order' => $order->id, 'status' => 'pending']),
            ],
            'auto_return'        => 'approved',
            'external_reference' => (string) $order->id,
        ];

        try {
            $preference = $client->create($preferenceData);
        } catch (MPApiException $e) {
            $statusCode = $e->getStatusCode();
            $content = $e->getApiResponse()?->getContent() ?? 'sin detalle';
            Log::error('MercadoPago preference error', [
                'status'  => $statusCode,
                'body'    => $content,
                'request' => $preferenceData,
            ]);
            return back()->with('error', 'No se pudo conectar con MercadoPago. Intenta de nuevo más tarde.');
        } catch (\Throwable $e) {
            Log::error('MercadoPago error inesperado', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al procesar el pago. Intenta de nuevo.');
        }

        $order->update(['mp_preference_id' => $preference->id]);

        return redirect($preference->init_point);
    }

    public function proPlanCallback(Request $request)
    {
        $order = ProOrder::findOrFail($request->query('order'));
        $status = $request->query('status');
        $paymentId = $request->query('payment_id');

        if ($paymentId) {
            $order->update(['mp_payment_id' => $paymentId]);
        }

        if ($status === 'success') {
            if ($order->status !== 'paid') {
                $order->update([
                    'status'    => 'paid',
                    'mp_status' => 'approved',
                    'paid_at'   => now(),
                ]);
                $this->activateProSubscription($order);
            }

            return redirect()->route('dashboard')->with('success', '¡Pago exitoso! Tu plan Pro ya está activo.');
        }

        if ($status === 'pending') {
            $order->update(['mp_status' => 'pending']);
            return redirect()->route('plans.pro')->with('success', 'Tu pago está pendiente de confirmación. Te notificaremos cuando se acredite.');
        }

        return redirect()->route('plans.pro')->with('error', 'El pago no se pudo completar. Puedes intentarlo de nuevo.');
    }

    public function plusPlan()
    {
        return view('plans.plus');
    }

    public function plusPlanSubmit(Request $request)
    {
        $validated = $request->validate(PlusRequestHelper::rules());
        $data      = PlusRequestHelper::withLabels($validated);

        // Guardar en BD (con manejo de error por si la migración no corrió aún)
        try {
            ProOrder::create([
                'user_id'        => Auth::id(),
                'full_name'      => $validated['requester_name'],
                'email'          => $validated['requester_email'],
                'amount_cents'   => 0,
                'currency'       => 'MXN',
                'billing_period' => 'monthly',
                'plan_slug'      => 'plus',
                'status'         => 'inquiry',
                'notes'          => $data,
            ]);
            Log::info('Solicitud Plus (web) guardada', [
                'name'  => $validated['requester_name'],
                'email' => $validated['requester_email'],
                'org'   => $validated['org_name'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar solicitud Plus (web)', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            // Continúa para enviar email aunque falle BD
        }

        // Email de confirmación al solicitante (con CC al admin)
        try {
            Mail::to($validated['requester_email'])
                ->send(new PlusRequestMail($data, 'confirmation'));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar email de confirmación Plus', ['error' => $e->getMessage()]);
        }

        return back()->with('success', '¡Solicitud enviada! Revisa tu correo — te contactaremos en menos de 24 horas.');
    }

    // ── Plus: pago directo (MercadoPago, usuarios individuales) ─────────────────

    public function plusPlanPaySubmit(Request $request)
    {
        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'billing_period' => 'required|in:monthly,annual',
            'accept_terms'   => 'required|accepted',
        ]);

        $isAnnual    = $validated['billing_period'] === 'annual';
        $amountCents = $isAnnual ? 199 * 12 * 100 * 0.85 : 19900; // anual con 15% descuento
        $amountCents = (int) round($amountCents);
        $amount      = $amountCents / 100;
        $title       = $isAnnual ? 'Mindra Plus — Suscripción Anual' : 'Mindra Plus — Suscripción Mensual';

        $order = ProOrder::create([
            'user_id'        => Auth::id(),
            'full_name'      => $validated['full_name'],
            'email'          => $validated['email'],
            'amount_cents'   => $amountCents,
            'currency'       => 'MXN',
            'billing_period' => $validated['billing_period'],
            'plan_slug'      => 'plus',
            'status'         => 'pending',
        ]);

        $accessToken = config('services.mercadopago.access_token');
        if (empty($accessToken)) {
            Log::error('MercadoPago Plus: access_token no configurado');
            return back()->with('error', 'Error de configuración del sistema de pagos.');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
        $client = new PreferenceClient();

        $preferenceData = [
            'items' => [[
                'id'          => 'plus-' . $order->id,
                'title'       => $title,
                'quantity'    => 1,
                'unit_price'  => (float) $amount,
                'currency_id' => 'MXN',
            ]],
            'payer' => [
                'name'  => $validated['full_name'],
                'email' => $validated['email'],
            ],
            'back_urls' => [
                'success' => route('plans.plus.callback', ['order' => $order->id, 'status' => 'success']),
                'failure' => route('plans.plus.callback', ['order' => $order->id, 'status' => 'failure']),
                'pending' => route('plans.plus.callback', ['order' => $order->id, 'status' => 'pending']),
            ],
            'auto_return'        => 'approved',
            'external_reference' => (string) $order->id,
        ];

        try {
            $preference = $client->create($preferenceData);
        } catch (MPApiException $e) {
            Log::error('MercadoPago Plus preference error', [
                'status' => $e->getStatusCode(),
                'body'   => $e->getApiResponse()?->getContent() ?? 'sin detalle',
            ]);
            return back()->with('error', 'No se pudo conectar con MercadoPago. Intenta de nuevo.');
        } catch (\Throwable $e) {
            Log::error('MercadoPago Plus error inesperado', ['message' => $e->getMessage()]);
            return back()->with('error', 'Error al procesar el pago.');
        }

        $order->update(['mp_preference_id' => $preference->id]);
        return redirect($preference->init_point);
    }

    public function plusPlanPayCallback(Request $request)
    {
        $order     = ProOrder::findOrFail($request->query('order'));
        $status    = $request->query('status');
        $paymentId = $request->query('payment_id');

        if ($paymentId) {
            $order->update(['mp_payment_id' => $paymentId]);
        }

        if ($status === 'success') {
            if ($order->status !== 'paid') {
                $order->update(['status' => 'paid', 'mp_status' => 'approved', 'paid_at' => now()]);
                $this->activatePlusSubscription($order);
            }
            return redirect()->route('dashboard')->with('success', '¡Pago exitoso! Tu plan Plus ya está activo.');
        }

        if ($status === 'pending') {
            $order->update(['mp_status' => 'pending']);
            return redirect()->route('plans.plus')->with('success', 'Tu pago está pendiente. Te notificaremos cuando se acredite.');
        }

        return redirect()->route('plans.plus')->with('error', 'El pago no se pudo completar. Puedes intentarlo de nuevo.');
    }

    public function plusPlanCallback(Request $request)
    {
        $order     = ProOrder::findOrFail($request->query('order'));
        $status    = $request->query('status');
        $paymentId = $request->query('payment_id');

        if ($paymentId) {
            $order->update(['mp_payment_id' => $paymentId]);
        }

        if ($status === 'success') {
            if ($order->status !== 'paid') {
                $order->update([
                    'status'    => 'paid',
                    'mp_status' => 'approved',
                    'paid_at'   => now(),
                ]);
                $this->activatePlusSubscription($order);
            }

            return redirect()->route('dashboard')->with('success', '¡Pago exitoso! Tu plan Plus ya está activo.');
        }

        if ($status === 'pending') {
            $order->update(['mp_status' => 'pending']);
            return redirect()->route('plans.plus')->with('success', 'Tu pago está pendiente de confirmación. Te notificaremos cuando se acredite.');
        }

        return redirect()->route('plans.plus')->with('error', 'El pago no se pudo completar. Puedes intentarlo de nuevo.');
    }

    private function activateProSubscription(ProOrder $order): void
    {
        if (!$order->user_id) return;

        $plan = Plan::where('slug', 'pro')->first();
        if (!$plan) return;

        Subscription::where('user_id', $order->user_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $days = $order->billing_period === 'annual' ? 365 : 30;

        $sub = Subscription::create([
            'user_id'    => $order->user_id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'provider'   => 'mercadopago',
            'external_subscription_id' => $order->mp_payment_id,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays($days),
        ]);

        // Email de activación al usuario
        $user = \App\Models\User::find($order->user_id);
        if ($user) {
            try {
                Mail::to($user->email)->send(new PlanActivatedMail(
                    $user, 'pro', 'Pro', $sub->effectiveFeatures()
                ));
            } catch (\Throwable $e) {
                Log::warning('Email activación Pro falló', ['user' => $user->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function activatePlusSubscription(ProOrder $order): void
    {
        if (!$order->user_id) {
            return;
        }

        $plan = Plan::where('slug', 'plus')->first();
        if (!$plan) {
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
