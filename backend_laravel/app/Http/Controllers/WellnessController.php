<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WellnessController extends Controller
{
    /**
     * GET /api/wellness/score
     *
     * Puntuación de bienestar compuesta (0–100, mayor = mejor bienestar).
     *
     * Componentes:
     *   • ansiedad     — 1 - avg(predicted_probability) de los últimos 7 días
     *   • estado_animo — avg(mood_score) normalizado 1-5 → 0-100
     *   • evaluacion   — último GAD-7 invertido y normalizado (0-21 → 100-0)
     *
     * Cacheado 15 min por usuario. Para forzar recálculo inmediato el cliente
     * puede añadir ?refresh=1, lo que invalida y recalcula la caché.
     *
     * Los componentes se omiten si no hay datos suficientes.
     * Si no hay ningún dato se devuelve score = null.
     */
    public function score(Request $request): JsonResponse
    {
        $user     = $request->user();
        $cacheKey = "wellness_score:{$user->id}";

        // Permitir invalidación manual (útil tras guardar diario o evaluación)
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 900, function () use ($user) {
            $since7 = now()->subDays(7);

            // ── Componente 1: ansiedad media (últimos 7 días) ─────────────────
            $avgAnxiety = $user->inferenceRecords()
                ->where('created_at', '>=', $since7)
                ->whereNotNull('predicted_probability')
                ->avg('predicted_probability');

            // ── Componente 2: estado de ánimo medio (últimos 7 días) ──────────
            $avgMood = $user->moodJournals()
                ->where('created_at', '>=', $since7)
                ->avg('mood_score');

            // ── Componente 3: último GAD-7 ────────────────────────────────────
            $lastGad7 = $user->assessments()
                ->where('type', 'gad7')
                ->latest()
                ->value('score');

            // ── Calcular puntuaciones por componente ──────────────────────────
            $components = [];

            if ($avgAnxiety !== null) {
                $components['ansiedad'] = (int) round((1 - $avgAnxiety) * 100);
            }

            if ($avgMood !== null) {
                $components['estado_animo'] = (int) round(($avgMood - 1) / 4 * 100);
            }

            if ($lastGad7 !== null) {
                $components['evaluacion'] = (int) round((1 - $lastGad7 / 21) * 100);
            }

            $score = !empty($components)
                ? (int) round(array_sum($components) / count($components))
                : null;

            return compact('score', 'components');
        });

        $score      = $data['score'];
        $components = $data['components'];

        return response()->json([
            'ok'          => true,
            'score'       => $score,
            'level'       => $this->level($score),
            'label'       => $this->label($score),
            'components'  => $components,
            'period'      => '7 días',
            'data_points' => count($components),
        ]);
    }

    private function level(?int $score): ?string
    {
        if ($score === null) return null;
        return match (true) {
            $score >= 80 => 'excelente',
            $score >= 60 => 'bueno',
            $score >= 40 => 'regular',
            $score >= 20 => 'bajo',
            default      => 'muy_bajo',
        };
    }

    private function label(?int $score): ?string
    {
        if ($score === null) return null;
        return match (true) {
            $score >= 80 => 'Excelente bienestar 🌟',
            $score >= 60 => 'Buen bienestar 😊',
            $score >= 40 => 'Bienestar regular 😐',
            $score >= 20 => 'Bienestar bajo 😕',
            default      => 'Bienestar muy bajo 😔',
        };
    }
}
