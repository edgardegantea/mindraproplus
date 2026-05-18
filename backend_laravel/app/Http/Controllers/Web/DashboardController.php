<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InferenceRecord;
use App\Models\VisitorSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Datos diarios para el calendario (todos los tiempos)
        $calendarData = InferenceRecord::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as day, AVG(predicted_probability) as avg_prob, COUNT(*) as cnt')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->day => ['avg' => round((float) $r->avg_prob * 100), 'cnt' => (int) $r->cnt],
            ]);

        // Sesiones con promedio y registros en orden cronológico
        $sessions = VisitorSession::where('user_id', $user->id)
            ->withCount('inferenceRecords')
            ->withAvg('inferenceRecords', 'predicted_probability')
            ->with(['inferenceRecords' => fn ($q) => $q->orderBy('created_at', 'asc')])
            ->latest()
            ->paginate(10);

        $totalInferences  = InferenceRecord::where('user_id', $user->id)->count();
        $avgProbability   = InferenceRecord::where('user_id', $user->id)->avg('predicted_probability');
        $highAnxietyCount = InferenceRecord::where('user_id', $user->id)
            ->where('predicted_probability', '>', 0.65)
            ->count();

        return view('dashboard.index', compact(
            'sessions',
            'totalInferences',
            'avgProbability',
            'highAnxietyCount',
            'calendarData',
        ));
    }

    public static function recommendations(float $pct): array
    {
        return match (true) {
            $pct > 65 => [
                'Considera hablar con un profesional de salud mental; no tienes que atravesar esto solo/a.',
                'Practica la respiración 4-7-8: inhala 4 s, sostén 7 s, exhala 8 s. Repite 4 veces.',
                'Limita el consumo de cafeína y alcohol; ambos amplifican la respuesta de estrés.',
                'Escribe en un diario: externalizar pensamientos reduce la carga cognitiva.',
                'Recuerda que puedes llamar a una línea de apoyo emocional si el malestar es intenso.',
            ],
            $pct > 40 => [
                'Dedica 10 minutos al día a meditación guiada; apps como Calm o Headspace ayudan.',
                'Sal a caminar 30 minutos; el ejercicio aeróbico moderado reduce el cortisol.',
                'Mantén un horario de sueño regular y evita pantallas 1 hora antes de dormir.',
                'Comparte cómo te sientes con alguien de confianza; verbalizar alivia.',
            ],
            default => [
                '¡Tu nivel de bienestar emocional está bien! Sigue así.',
                'Continúa practicando la autoconciencia: identifica qué actividades te generan calma.',
                'Una caminata breve diaria refuerza el bienestar emocional a largo plazo.',
            ],
        };
    }
}
