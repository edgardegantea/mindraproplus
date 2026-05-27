<?php

namespace App\Providers;

use App\Models\InferenceRecord;
use App\Observers\InferenceRecordObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        InferenceRecord::observe(InferenceRecordObserver::class);
    }
}
