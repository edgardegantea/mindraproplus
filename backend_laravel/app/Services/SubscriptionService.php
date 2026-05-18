<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    public function resolvePlan(string $slug): Plan
    {
        return Plan::where('slug', $slug)->firstOrFail();
    }

    public function createSubscription(User $user, Plan $plan, ?string $provider = 'stripe'): Subscription
    {
        $expiresAt = $plan->price_cents === 0
            ? null
            : Carbon::now()->addDays($plan->trial_days ?? 30);

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'provider' => $provider,
            'started_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);
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
}
