<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Los componentes se omiten si no hay datos suficientes.
     * Si no hay ningún dato se devuelve score = null.
     */
    public function score(Request $request): JsonResponse
    {
        $user   = $request->user();
        $since7 = now()->subDays(7);

        // ── Componente 1: ansiedad media (últimos 7 días) ─────────────────────
        $avgAnxiety = $user->inferenceRecords()
            ->where('created_at', '>=', $since7)
            ->whereNotNull('predicted_probability')
            ->avg('predicted_probability');

        // ── Componente 2: estado de ánimo medio (últimos 7 días) ──────────────
        $avgMood = $user->moodJournals()
            ->where('created_at', '>=', $since7)
            ->avg('mood_score');

        // ── Componente 3: último GAD-7 ────────────────────────────────────────
        $lastGad7 = $user->assessments()
            ->where('type', 'gad7')
            ->latest()
            ->value('score');

        // ── Calcular puntuaciones por componente ──────────────────────────────
        $components = [];

        if ($avgAnxiety !== null) {
            // 0 ansiedad → 100 pts · 1 (máxima) → 0 pts
            $components['ansiedad'] = (int) round((1 - $avgAnxiety) * 100);
        }

        if ($avgMood !== null) {
            // escala 1-5 → 0-100
            $components['estado_animo'] = (int) round(($avgMood - 1) / 4 * 100);
        }

        if ($lastGad7 !== null) {
            // 0 → 100 pts · 21 (máximo) → 0 pts
            $components['evaluacion'] = (int) round((1 - $lastGad7 / 21) * 100);
        }

        $score = !empty($components)
            ? (int) round(array_sum($components) / count($components))
            : null;

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
