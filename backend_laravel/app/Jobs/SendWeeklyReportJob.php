<?php

namespace App\Jobs;

use App\Mail\WeeklyReportMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job que envía el reporte semanal de bienestar a un usuario individual.
 *
 * Se despacha desde SendWeeklyReports command y se ejecuta en background
 * usando la conexión de cola configurada (database por defecto).
 *
 * Reintentos: 3 intentos con backoff exponencial (60s, 180s, 540s).
 */
class SendWeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly User  $user,
        public readonly array $stats,
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WeeklyReportMail($this->user, $this->stats));

        Log::info('[WeeklyReport] Email enviado.', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[WeeklyReport] Falló el envío del email.', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
            'error'   => $e->getMessage(),
        ]);
    }

    /** Backoff exponencial: 60s · 180s · 540s */
    public function backoff(): array
    {
        return [60, 180, 540];
    }
}
