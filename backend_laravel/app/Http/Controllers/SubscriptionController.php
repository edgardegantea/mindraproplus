<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function current(Request $request)
    {
        $subscription = $this->subscriptionService->getActiveSubscription($request->user());

        return response()->json([
            'ok' => true,
            'subscription' => $subscription,
        ]);
    }

    public function subscribe(SubscriptionRequest $request)
    {
        $plan = $this->subscriptionService->resolvePlan($request->validated('plan_slug'));

        $subscription = $this->subscriptionService->createSubscription(
            $request->user(),
            $plan
        );

        return response()->json([
            'ok' => true,
            'message' => 'Suscripción creada correctamente.',
            'subscription' => $subscription,
        ], 201);
    }
}
