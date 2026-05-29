<?php

namespace App\Http\Controllers;

use App\Http\Requests\InferenceRequest;
use App\Mail\CrisisAlertMail;
use App\Mail\PlusRequestMail;
use App\Models\CrisisEvent;
use App\Models\ProOrder;
use App\Services\InferenceService;
use App\Models\InferenceRecord;
use App\Support\PlusRequestHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InferenceController extends Controller
{
    protected InferenceService $inferenceService;

    public function __construct(InferenceService $inferenceService)
    {
        $this->inferenceService = $inferenceService;
    }

    public function predict(InferenceRequest $request)
    {
        $user   = $request->user();
        $result = $this->inferenceService->predict(
            $user,
            $request->file('audio'),
            $request->input('texto', ''),
            $request->file('image'),
            $request->input('duration_seconds') ? (float) $request->input('duration_seconds') : null
        );

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'error' => $result['error']], 400);
        }

        // ── Crisis detection: loguear y enviar email si ansiedad > 75% ───────
        $prob = $result['probabilidad_ansiedad'] ?? 0;
        if ($prob > 0.75 && $user && isset($result['record'])) {
            $this->handleCrisisEvent($user, $result['record'], $prob, $result['etiqueta'] ?? '');
        }

        return response()->json($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registra un evento de crisis y envía email si el usuario tiene alertas
    // ─────────────────────────────────────────────────────────────────────────
    protected function handleCrisisEvent($user, InferenceRecord $record, float $prob, string $label): void
    {
        try {
            $emailSent = false;

            // Verificar preferencia del usuario para alertas de crisis
            $pref = $user->notificationPreference;
            $wantsEmail = $pref ? $pref->crisis_alerts : false;

            if ($wantsEmail) {
                Mail::to($user->email)->send(new CrisisAlertMail($user, $record));
                $emailSent = true;
            }

            CrisisEvent::create([
                'user_id'             => $user->id,
                'inference_record_id' => $record->id,
                'probability'         => $prob,
                'predicted_label'     => $label,
                'email_sent'          => $emailSent,
                'email_sent_at'       => $emailSent ? now() : null,
                'notes'               => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);
        } catch (\Throwable $e) {
            // No interrumpir la respuesta al usuario si el log de crisis falla
            Log::error('CrisisEvent log failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/inference/transcribe
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribe(Request $request)
    {
        // 20 MB limit (vs. 10 MB in InferenceRequest) — intentional:
        // transcribe-only uploads may be longer recordings that don't need
        // inference, so a larger ceiling is acceptable here.
        $request->validate([
            'audio'    => 'required|file|mimes:mp3,wav,m4a,aac,ogg,mp4,webm|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/mp4,audio/aac,audio/ogg,audio/webm,video/webm|max:20480',
            'language' => 'nullable|string|max:10',
        ]);

        $result = $this->inferenceService->transcribe(
            $request->file('audio'),
            $request->input('language', 'es')
        );

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'error' => $result['error']], 503);
        }

        return response()->json($result);
    }

    public function history(Request $request)
    {
        $user     = $request->user();
        $features = $user->features();

        $isPlus  = !empty($features['imagen']) || !empty($features['estadisticas']);
        $perPage = max(1, min((int) $request->input('per_page', 20), 50));

        $cols = ['id', 'created_at', 'input_text', 'generated_text',
                 'predicted_label', 'predicted_probability',
                 'emotion_label', 'emotion_probability'];

        if (!$isPlus) {
            // Pro: historial acotado a los últimos 100 registros
            $topIds = $user->inferenceRecords()->latest()->take(100)->pluck('id');

            if ($topIds->isEmpty()) {
                return response()->json([
                    'ok'         => true,
                    'history'    => [],
                    'pagination' => ['current_page' => 1, 'last_page' => 1,
                                     'per_page' => $perPage, 'total' => 0],
                    'plan_limit' => 100,
                ]);
            }

            $result = $user->inferenceRecords()
                ->whereIn('id', $topIds)
                ->latest()
                ->select($cols)
                ->paginate($perPage);
        } else {
            $result = $user->inferenceRecords()
                ->latest()
                ->select($cols)
                ->paginate($perPage);
        }

        return response()->json([
            'ok'         => true,
            'history'    => $result->items(),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
            ],
            'plan_limit' => $isPlus ? null : 100,
        ]);
    }

    /**
     * Calendario de bienestar: ansiedad promedio agrupada por día (últimos 60 días).
     * Responde: { ok, calendar: [ { date: "2026-05-01", avg_anxiety: 0.42, count: 3 }, ... ] }
     */
    public function calendar(Request $request)
    {
        $rows = $request->user()
            ->inferenceRecords()
            ->where('created_at', '>=', now()->subDays(60))
            ->whereNotNull('predicted_probability')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(predicted_probability) as avg_anxiety'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json(['ok' => true, 'calendar' => $rows]);
    }

    /**
     * Tendencias: promedio semanal de ansiedad (últimas 12 semanas) y distribución de emociones.
     * Responde: { ok, weekly: [...], emotions: [...] }
     */
    public function trends(Request $request)
    {
        $weekly = $request->user()
            ->inferenceRecords()
            ->where('created_at', '>=', now()->subWeeks(12))
            ->whereNotNull('predicted_probability')
            ->select(
                DB::raw('YEARWEEK(created_at, 1) as week'),
                DB::raw('MIN(DATE(created_at)) as week_start'),
                DB::raw('AVG(predicted_probability) as avg_anxiety'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('YEARWEEK(created_at, 1)'))
            ->orderBy('week')
            ->get();

        $emotions = $request->user()
            ->inferenceRecords()
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('emotion_label')
            ->select('emotion_label', DB::raw('COUNT(*) as total'))
            ->groupBy('emotion_label')
            ->orderByDesc('total')
            ->get();

        return response()->json(['ok' => true, 'weekly' => $weekly, 'emotions' => $emotions]);
    }

    /**
     * Reporte semanal: resumen de la última semana para exportar como PDF desde la app.
     * Responde: { ok, report: { period, total_sessions, avg_anxiety, peak_day, emotions, records } }
     */
    public function weeklyReport(Request $request)
    {
        $from = now()->subDays(7)->startOfDay();

        $records = $request->user()
            ->inferenceRecords()
            ->where('created_at', '>=', $from)
            ->latest()
            ->get(['id', 'created_at', 'input_text', 'predicted_label', 'predicted_probability', 'emotion_label']);

        $avgAnxiety = $records->whereNotNull('predicted_probability')->avg('predicted_probability');

        $byDay = $records->whereNotNull('predicted_probability')
            ->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
            ->map(fn($g) => $g->avg('predicted_probability'));

        $peakDay = $byDay->sortDesc()->keys()->first();

        $emotions = $records->whereNotNull('emotion_label')
            ->groupBy('emotion_label')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        return response()->json([
            'ok' => true,
            'report' => [
                'period'         => ['from' => $from->toDateString(), 'to' => now()->toDateString()],
                'total_sessions' => $records->count(),
                'avg_anxiety'    => round((float)($avgAnxiety ?? 0), 3),
                'peak_day'       => $peakDay,
                'emotions'       => $emotions->isEmpty() ? new \stdClass() : $emotions,
                'records'        => $records->take(20)->values(),
            ],
        ]);
    }

    /**
     * Reporte clínico completo (Plus): historial extendido + estadísticas + patrones.
     * Responde: { ok, clinical: { user, period, summary, emotion_distribution, anxiety_timeline, records } }
     */
    public function clinicalReport(Request $request)
    {
        $user = $request->user();
        $from = now()->subDays(30)->startOfDay();

        $records = $user->inferenceRecords()
            ->where('created_at', '>=', $from)
            ->latest()
            ->get(['id', 'created_at', 'input_text', 'predicted_label',
                   'predicted_probability', 'emotion_label', 'emotion_probability',
                   'transcription_source', 'audio_duration_seconds']);

        $withProb = $records->whereNotNull('predicted_probability');
        $avgAnxiety  = round((float)($withProb->avg('predicted_probability') ?? 0), 3);
        $maxAnxiety  = round((float)($withProb->max('predicted_probability') ?? 0), 3);
        $crisisCount = $withProb->where('predicted_probability', '>', 0.75)->count();

        // Distribución de emociones
        $emotionDist = $records->whereNotNull('emotion_label')
            ->groupBy('emotion_label')
            ->map(fn($g) => [
                'count'   => $g->count(),
                'avg_confidence' => round((float)$g->avg('emotion_probability'), 3),
            ]);

        // Timeline de ansiedad diario
        $timeline = $records->whereNotNull('predicted_probability')
            ->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
            ->map(fn($g) => [
                'avg'   => round((float)$g->avg('predicted_probability'), 3),
                'max'   => round((float)$g->max('predicted_probability'), 3),
                'count' => $g->count(),
            ]);

        // Fuentes de entrada
        $sources = $records->groupBy('transcription_source')
            ->map(fn($g) => $g->count());

        return response()->json([
            'ok' => true,
            'clinical' => [
                'user'   => ['name' => $user->name, 'email' => $user->email],
                'period' => ['from' => $from->toDateString(), 'to' => now()->toDateString()],
                'summary' => [
                    'total_sessions'  => $records->count(),
                    'avg_anxiety'     => $avgAnxiety,
                    'max_anxiety'     => $maxAnxiety,
                    'crisis_episodes' => $crisisCount,
                    'input_sources'   => $sources,
                ],
                'emotion_distribution' => $emotionDist->isEmpty() ? new \stdClass() : $emotionDist,
                'anxiety_timeline'     => $timeline->isEmpty() ? new \stdClass() : $timeline,
                'records'              => $records->values(),
            ],
        ]);
    }

    /**
     * Recibe una solicitud de acceso al plan Plus desde la app móvil.
     */
    public function plusRequest(Request $request)
    {
        $validated = $request->validate(PlusRequestHelper::rules());
        $data      = PlusRequestHelper::withLabels($validated);
        $data['source'] = 'mobile_app';

        try {
            ProOrder::create([
                'user_id'        => $request->user()?->id,
                'full_name'      => $validated['requester_name'],
                'email'          => $validated['requester_email'],
                'amount_cents'   => 0,
                'currency'       => 'MXN',
                'billing_period' => 'monthly',
                'plan_slug'      => 'plus',
                'status'         => 'inquiry',
                'notes'          => $data,
            ]);
            Log::info('Solicitud Plus (app móvil) guardada', [
                'name'  => $validated['requester_name'],
                'email' => $validated['requester_email'],
                'org'   => $validated['org_name'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar solicitud Plus (app)', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
        }

        // Email de confirmación al solicitante (con CC al admin)
        try {
            Mail::to($validated['requester_email'])
                ->send(new PlusRequestMail($data, 'confirmation'));
        } catch (\Throwable $e) {
            Log::warning('Email confirmación Plus (app) falló', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Solicitud recibida. Revisa tu correo — te contactaremos en menos de 24 horas.',
        ]);
    }

    public function stats()
    {
        $this->authorize('viewAny', InferenceRecord::class);

        $totalUsers = InferenceRecord::whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $totalInferences = InferenceRecord::count();
        $avgDuration = InferenceRecord::avg('audio_duration_seconds');
        $avgProbability = InferenceRecord::avg('predicted_probability');
        $byLabel = InferenceRecord::select('predicted_label', DB::raw('count(*) as total'))
            ->groupBy('predicted_label')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'ok' => true,
            'total_users' => $totalUsers,
            'total_inferences' => $totalInferences,
            'avg_audio_duration_seconds' => $avgDuration,
            'avg_predicted_probability' => $avgProbability,
            'by_label' => $byLabel,
        ]);
    }
}
