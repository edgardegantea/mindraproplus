<?php

namespace App\Providers;

use App\Models\InferenceRecord;
use App\Models\Plan;
use App\Policies\InferencePolicy;
use App\Policies\PlanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        InferenceRecord::class => InferencePolicy::class,
        Plan::class => PlanPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-plans', fn ($user) => $user->isAdmin());
    }
}
