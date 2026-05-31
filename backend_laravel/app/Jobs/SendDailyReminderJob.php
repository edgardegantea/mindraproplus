<?php

namespace App\Jobs;

use App\Models\DeviceToken;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Envía un recordatorio diario de bienestar a los usuarios que:
 *   1. Tienen tokens de dispositivo registrados
 *   2. No han hecho una inferencia en las últimas 20 horas
 *   3. No han desactivado los recordatorios de bienestar en sus preferencias
 *
 * Programado en routes/console.php — se ejecuta diariamente a las 09:00.
 */
class SendDailyReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(FcmService $fcm): void
    {
        $messages = [
            '¿Cómo te sientes hoy? Tómate un momento para registrar tu estado de ánimo.',
            'Tu bienestar importa. 💙 Mindra está aquí para escucharte.',
            'Un pequeño check-in puede hacer una gran diferencia. ¿Cómo estás?',
        ];
        $message = $messages[array_rand($messages)];

        // Usuarios con tokens que no han usado la app en las últimas 20 horas
        $userIds = DeviceToken::select('user_id')
            ->distinct()
            ->whereDoesntHave('user.inferenceRecords', fn ($q) =>
                $q->where('created_at', '>=', now()->subHours(20))
            )
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if (!$user) {
                continue;
            }

            // Respetar preferencia de recordatorio (si la tabla existe y está desactivada, saltar)
            try {
                $pref = $user->notificationPreference;
                if ($pref && $pref->wellness_reminder === false) {
                    continue;
                }
            } catch (\Throwable) {
                // La tabla o el campo no existen aún — continuar sin filtrar
            }

            $fcm->sendToUser($user, 'Mindra te recuerda 💙', $message, ['type' => 'daily_reminder']);
        }
    }
}
