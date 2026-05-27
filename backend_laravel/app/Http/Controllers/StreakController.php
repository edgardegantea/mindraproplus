<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    /**
     * Devuelve la racha actual, la racha más larga y el total de días activos.
     * Un día se considera "activo" si el usuario registró en el diario emocional
     * O realizó al menos una inferencia.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Limitar a los últimos 2 años para no cargar toda la historia de
        // usuarios con años de actividad (la racha nunca supera este rango).
        $since = now()->subYears(2)->startOfDay();

        // Días únicos con actividad
        $journalDays = $user->moodJournals()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day')
            ->distinct()
            ->pluck('day');

        $inferenceDays = $user->inferenceRecords()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day')
            ->distinct()
            ->pluck('day');

        $allDays = $journalDays
            ->merge($inferenceDays)
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($d) => Carbon::parse($d)->toDateString());

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        if ($allDays->isEmpty()) {
            return response()->json([
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_days'     => 0,
                'active_today'   => false,
            ]);
        }

        // Calcular racha más larga
        $longestStreak = 1;
        $tempStreak    = 1;

        for ($i = 1; $i < $allDays->count(); $i++) {
            $prev = Carbon::parse($allDays[$i - 1]);
            $curr = Carbon::parse($allDays[$i]);
            if ($curr->diffInDays($prev) === 1) {
                $tempStreak++;
                $longestStreak = max($longestStreak, $tempStreak);
            } else {
                $tempStreak = 1;
            }
        }

        // Calcular racha actual (solo si hoy o ayer fue activo)
        $currentStreak = 0;
        $lastDay       = $allDays->last();

        if ($lastDay === $today || $lastDay === $yesterday) {
            $currentStreak = 1;
            for ($i = $allDays->count() - 2; $i >= 0; $i--) {
                $next = Carbon::parse($allDays[$i + 1]);
                $curr = Carbon::parse($allDays[$i]);
                if ($next->diffInDays($curr) === 1) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
        }

        return response()->json([
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_days'     => $allDays->count(),
            'active_today'   => $lastDay === $today,
        ]);
    }
}
