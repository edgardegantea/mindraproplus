<?php

namespace App\Observers;

use App\Mail\CrisisAlertMail;
use App\Models\InferenceRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InferenceRecordObserver
{
    /**
     * Después de crear un registro de inferencia, disparar alerta de crisis
     * si la probabilidad supera 0.75 y el usuario tiene la feature crisis_alerts.
     */
    public function created(InferenceRecord $record): void
    {
        // Solo si hay usuario autenticado y probabilidad alta
        if (!$record->user_id || ($record->predicted_probability ?? 0) < 0.75) {
            return;
        }

        $user = $record->user;
        if (!$user) {
            return;
        }

        // 1. Verificar feature crisis_alerts en el plan activo (requiere Plus)
        $features = $user->features();
        if (empty($features['crisis_alerts'])) {
            return;
        }

        // 2. Verificar preferencia personal del usuario (puede haberla desactivado)
        $prefs = \App\Models\NotificationPreference::where('user_id', $record->user_id)->first();
        if ($prefs && !$prefs->crisis_alerts) {
            return;
        }

        // Evitar spam: solo una alerta por usuario cada 2 horas
        $recentAlert = \App\Models\InferenceRecord::where('user_id', $record->user_id)
            ->where('id', '!=', $record->id)
            ->where('predicted_probability', '>', 0.75)
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();

        if ($recentAlert) {
            return;
        }

        try {
            // queue() en lugar de send() — el observer corre justo después de
            // crear el registro y bloquearía la respuesta de inferencia si el
            // mailer tarda (SMTP timeout, red lenta, etc.).
            Mail::to($user->email)->queue(new CrisisAlertMail($user, $record));
        } catch (\Throwable $e) {
            Log::warning('CrisisAlertMail falló', [
                'user_id'   => $user->id,
                'record_id' => $record->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
