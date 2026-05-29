<?php

namespace App\Services;

use App\Models\InferenceRecord;
use App\Models\User;

/**
 * Servicio de memoria contextual para el chatbot.
 *
 * Analiza el historial reciente del usuario y genera un prefijo personalizado
 * que se añade a la respuesta del bot, haciendo la conversación continua y
 * contextual en lugar de tratarla como sesiones aisladas.
 */
class BotMemoryService
{
    private const MEMORY_SESSIONS = 5;  // Sesiones a recordar
    private const MIN_SESSIONS    = 3;  // Mínimo para activar la memoria

    /**
     * Genera un enriquecimiento de contexto para la respuesta del bot.
     * Devuelve null si no hay suficiente historial o si no es útil.
     */
    public function buildContext(User $user, float $currentProb, string $currentText): ?string
    {
        $history = InferenceRecord::where('user_id', $user->id)
            ->whereNotNull('predicted_probability')
            ->latest()
            ->take(self::MEMORY_SESSIONS)
            ->get();

        if ($history->count() < self::MIN_SESSIONS) {
            return null;
        }

        $avgProb      = $history->avg('predicted_probability');
        $trend        = $this->detectTrend($history);
        $activeDays   = $history->groupBy(fn ($r) => $r->created_at->toDateString())->count();
        $streakMessage = $this->buildStreakMessage($user, $activeDays);

        // Solo agregar contexto cuando sea relevante
        $enhancement = match(true) {
            // Mejora notable: usuario estaba peor antes
            ($avgProb > 0.6 && $currentProb < 0.45)
                => "He notado que últimamente pareces sentirte mejor que en sesiones anteriores. ",

            // Empeoramiento: usuario estaba mejor antes
            ($avgProb < 0.4 && $currentProb > 0.6)
                => "Noto que hoy pareces estar pasando un momento más difícil de lo usual. ",

            // Tendencia consistentemente alta
            ($trend === 'high' && $currentProb > 0.55)
                => "Llevamos varias sesiones donde la ansiedad aparece de forma recurrente. ",

            // Tendencia mejorando
            ($trend === 'improving')
                => "He observado que en las últimas sesiones has ido mejorando gradualmente. ",

            // Tendencia estable y baja — validar el progreso
            ($trend === 'stable_low')
                => "Tu constancia con Mindra está dando resultados. ",

            default => null,
        };

        // Agregar mensaje de racha si aplica
        $finalContext = ($enhancement ?? '') . ($streakMessage ?? '');

        return $finalContext ?: null;
    }

    /**
     * Genera la respuesta final del bot integrando el contexto de memoria.
     */
    public function enhanceResponse(string $baseResponse, ?string $context): string
    {
        if (!$context) {
            return $baseResponse;
        }

        // Integrar el contexto de forma natural antes de la respuesta base
        return $context . $baseResponse;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function detectTrend($history): string
    {
        $probs = $history->pluck('predicted_probability')->reverse()->values();

        if ($probs->count() < 3) return 'unknown';

        $first = $probs->take(2)->avg();
        $last  = $probs->slice(-2)->avg();
        $avg   = $probs->avg();

        if ($last < $first - 0.12) return 'improving';
        if ($last > $first + 0.12) return 'worsening';
        if ($avg > 0.60)           return 'high';
        if ($avg < 0.35)           return 'stable_low';

        return 'stable';
    }

    private function buildStreakMessage(User $user, int $activeDays): ?string
    {
        if ($activeDays >= 5) {
            return "Llevas {$activeDays} días activo/a esta semana. Esa constancia importa. ";
        }
        return null;
    }
}
