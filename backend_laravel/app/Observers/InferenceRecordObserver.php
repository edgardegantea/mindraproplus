<?php

namespace App\Observers;

use App\Mail\CrisisAlertMail;
use App\Models\CrisisEvent;
use App\Models\InferenceRecord;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InferenceRecordObserver
{
    /**
     * Única fuente de verdad para el manejo de crisis.
     *
     * Lógica:
     *  1. Siempre registra un CrisisEvent cuando prob > 0.75 (cualquier plan).
     *  2. Solo envía email si el usuario tiene la feature "crisis_alerts" (Plus)
     *     Y la preferencia personal activada.
     *  3. Throttle: máximo 1 email / 2 horas por usuario (usa CrisisEvents previos).
     *  4. Todo asíncrono (queue) para no bloquear la respuesta de inferencia.
     */
    public function created(InferenceRecord $record): void
    {
        if (!$record->user_id || ($record->predicted_probability ?? 0) < 0.75) {
            return;
        }

        $user = $record->user;
        if (!$user) {
            return;
        }

        // Envolver en try/catch para que una tabla faltante (pre-migración) o
        // cualquier otro error NO revierta la transacción de InferenceRecord
        // ni devuelva un 500 al usuario.
        try {
            // ── Throttle: ¿hubo ya un CrisisEvent en las últimas 2 horas? ─────
            $throttled = CrisisEvent::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHours(2))
                ->exists();

            // ── Decidir si enviar email ────────────────────────────────────────
            $emailSent = false;

            if (!$throttled) {
                $features  = $user->features();
                $wantsEmail = !empty($features['crisis_alerts']);

                if ($wantsEmail) {
                    $prefs      = NotificationPreference::where('user_id', $user->id)->first();
                    $wantsEmail = $prefs ? (bool) $prefs->crisis_alerts : false;
                }

                if ($wantsEmail) {
                    try {
                        Mail::to($user->email)->queue(new CrisisAlertMail($user, $record));
                        $emailSent = true;
                    } catch (\Throwable $e) {
                        Log::warning('[Observer] CrisisAlertMail falló', [
                            'user_id'   => $user->id,
                            'record_id' => $record->id,
                            'error'     => $e->getMessage(),
                        ]);
                    }
                }
            }

            // ── Push notification si hay tokens registrados ───────────────────
            if ($emailSent || !$throttled) {
                app(\App\Services\FcmService::class)->sendToUser(
                    $user,
                    '⚠️ Alerta de bienestar',
                    'Mindra detectó indicadores de ansiedad elevada. ¿Cómo te encuentras?',
                    ['type' => 'crisis_alert', 'record_id' => (string) $record->id]
                );
            }

            // ── Registrar el evento para auditoría (independiente de plan) ────
            CrisisEvent::create([
                'user_id'             => $user->id,
                'inference_record_id' => $record->id,
                'probability'         => $record->predicted_probability,
                'predicted_label'     => $record->predicted_label ?? '',
                'email_sent'          => $emailSent,
                'email_sent_at'       => $emailSent ? now() : null,
                'notes'               => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'throttled'  => $throttled,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[Observer] CrisisEvent handling failed', [
                'user_id'   => $user->id,
                'record_id' => $record->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
