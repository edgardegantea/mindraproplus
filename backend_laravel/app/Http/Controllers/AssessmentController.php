<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /** Últimas 10 evaluaciones del usuario. */
    public function index(Request $request): JsonResponse
    {
        $assessments = $request->user()
            ->assessments()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($a) => $this->format($a));

        return response()->json(['assessments' => $assessments]);
    }

    /** Evaluación más reciente de tipo GAD-7. */
    public function latest(Request $request): JsonResponse
    {
        $assessment = $request->user()
            ->assessments()
            ->where('type', 'gad7')
            ->latest()
            ->first();

        return response()->json([
            'assessment' => $assessment ? $this->format($assessment) : null,
        ]);
    }

    /** Guarda una nueva evaluación y devuelve resultado con interpretación. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:gad7,phq9',
            'answers'     => 'required|array',
            'answers.*'   => 'integer|min:0|max:3',
        ]);

        $minItems = $data['type'] === 'phq9' ? 9 : 7;
        if (count($data['answers']) < $minItems) {
            return response()->json([
                'ok'    => false,
                'error' => "Se requieren al menos {$minItems} respuestas para {$data['type']}.",
            ], 422);
        }

        $score    = array_sum($data['answers']);
        $severity = Assessment::severityFromScore($score, $data['type']);

        $assessment = $request->user()->assessments()->create([
            'type'     => $data['type'],
            'score'    => $score,
            'answers'  => $data['answers'],
            'severity' => $severity,
        ]);

        return response()->json(['assessment' => $this->format($assessment)], 201);
    }

    /**
     * GET /api/assessments/trends
     *
     * Historial de scores GAD-7 y PHQ-9 de los últimos 90 días,
     * agrupado por tipo. Útil para graficar progreso en la app.
     *
     * Responde: { ok, gad7: [...], phq9: [...], period }
     */
    public function trends(Request $request): JsonResponse
    {
        $user  = $request->user();
        $since = now()->subDays(90);

        $byType = $user->assessments()
            ->where('created_at', '>=', $since)
            ->orderBy('created_at')
            ->get(['type', 'score', 'severity', 'created_at'])
            ->groupBy('type')
            ->map(fn ($items) => $items->map(fn ($a) => [
                'score'          => $a->score,
                'severity'       => $a->severity,
                'severity_label' => Assessment::severityLabel($a->severity),
                'created_at'     => $a->created_at->toIso8601String(),
            ])->values());

        return response()->json([
            'ok'     => true,
            'gad7'   => $byType->get('gad7', collect())->values(),
            'phq9'   => $byType->get('phq9', collect())->values(),
            'period' => '90 días',
        ]);
    }

    private function format(Assessment $a): array
    {
        return [
            'id'             => $a->id,
            'type'           => $a->type,
            'score'          => $a->score,
            'severity'       => $a->severity,
            'severity_label' => Assessment::severityLabel($a->severity),
            'answers'        => $a->answers,
            'created_at'     => $a->created_at->toIso8601String(),
        ];
    }
}
