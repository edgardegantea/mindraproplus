<?php

use App\Console\Commands\SendWeeklyReports;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reporte semanal de bienestar — todos los lunes a las 8:00 AM (hora México)
Schedule::command(SendWeeklyReports::class)
    ->weeklyOn(1, '08:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/weekly_reports.log'));
