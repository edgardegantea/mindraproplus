<?php

namespace App\Http\Controllers;

use App\Models\MoodJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoodJournalController extends Controller
{
    /**
     * GET /api/journal
     *
     * Lista el diario emocional con paginación y filtros opcionales.
     *
     * Query params:
     *   page       (int)    — página (default: 1)
     *   per_page   (int)    — resultados por página, 1-50 (default: 20)
     *   from       (date)   — filtrar desde esta fecha (YYYY-MM-DD)
     *   to         (date)   — filtrar hasta esta fecha (YYYY-MM-DD)
     *   mood_score (int)    — filtrar por puntuación exacta (1-5)
     *   search     (string) — buscar en nota o etiquetas
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page'   => 'nullable|integer|min:1|max:50',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date|after_or_equal:from',
            'mood_score' => 'nullable|integer|min:1|max:5',
            'search'     => 'nullable|string|max:100',
        ]);

        $query = MoodJournal::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->select(['id', 'mood_score', 'mood_emoji', 'mood_label', 'note', 'tags', 'created_at']);

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($moodScore = $request->input('mood_score')) {
            $query->where('mood_score', (int) $moodScore);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', '%' . $search . '%')
                  ->orWhereJsonContains('tags', $search);
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $result  = $query->paginate($perPage);

        return response()->json([
            'journal'    => $result->items(),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
            ],
        ]);
    }

    /** POST /api/journal — crear entrada. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood_score' => 'required|integer|min:1|max:5',
            'note'       => 'nullable|string|max:1000',
            'tags'       => 'nullable|array|max:10',
            'tags.*'     => 'string|max:50',
        ]);

        $meta = MoodJournal::fromScore((int) $validated['mood_score']);

        $entry = MoodJournal::create([
            'user_id'    => $request->user()->id,
            'mood_score' => $validated['mood_score'],
            'mood_emoji' => $meta['emoji'],
            'mood_label' => $meta['label'],
            'note'       => $validated['note'] ?? null,
            'tags'       => $validated['tags'] ?? null,
        ]);

        return response()->json(['entry' => $entry], 201);
    }

    /** DELETE /api/journal/{id} — eliminar entrada propia. */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = MoodJournal::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $entry->delete();

        return response()->json(['ok' => true]);
    }

    /** GET /api/journal/today — entrada de hoy (si existe). */
    public function today(Request $request): JsonResponse
    {
        $entry = MoodJournal::where('user_id', $request->user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->first(['id', 'mood_score', 'mood_emoji', 'mood_label', 'note', 'created_at']);

        return response()->json(['entry' => $entry]);
    }

    /**
     * GET /api/journal/stats
     *
     * Estadísticas del diario emocional (últimos 30 días).
     * Útil para el dashboard de la app.
     *
     * Responde: { total, avg_score, distribution, daily_avg }
     */
    public function stats(Request $request): JsonResponse
    {
        $user    = $request->user();
        $since30 = now()->subDays(30);

        $entries = $user->moodJournals()
            ->where('created_at', '>=', $since30)
            ->get(['mood_score', 'created_at']);

        if ($entries->isEmpty()) {
            return response()->json([
                'ok'           => true,
                'period'       => '30 días',
                'total'        => 0,
                'avg_score'    => null,
                'distribution' => [],
                'daily_avg'    => (object) [],
            ]);
        }

        // Distribución por puntuación (1-5)
        $distribution = $entries->countBy('mood_score')
            ->map(fn ($count, $score) => [
                'score'   => (int) $score,
                'label'   => MoodJournal::fromScore((int) $score)['label'],
                'emoji'   => MoodJournal::fromScore((int) $score)['emoji'],
                'count'   => $count,
                'percent' => round($count / $entries->count() * 100, 1),
            ])
            ->sortKeys()
            ->values();

        // Promedio diario
        $dailyAvg = $entries
            ->groupBy(fn ($e) => $e->created_at->format('Y-m-d'))
            ->map(fn ($g) => round($g->avg('mood_score'), 1))
            ->sortKeys();

        return response()->json([
            'ok'           => true,
            'period'       => '30 días',
            'total'        => $entries->count(),
            'avg_score'    => round($entries->avg('mood_score'), 2),
            'distribution' => $distribution->toArray(),
            'daily_avg'    => $dailyAvg,
        ]);
    }
}
