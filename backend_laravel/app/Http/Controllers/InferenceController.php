<?php

namespace App\Http\Controllers;

use App\Http\Requests\InferenceRequest;
use App\Services\InferenceService;
use App\Models\InferenceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InferenceController extends Controller
{
    protected InferenceService $inferenceService;

    public function __construct(InferenceService $inferenceService)
    {
        $this->inferenceService = $inferenceService;
    }

    public function predict(InferenceRequest $request)
    {
        $result = $this->inferenceService->predict(
            $request->user(),
            $request->file('audio'),
            $request->input('texto', ''),
            $request->file('image'),
            $request->input('duration_seconds') ? (float) $request->input('duration_seconds') : null
        );

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'error' => $result['error']], 400);
        }

        return response()->json($result);
    }

    public function history(Request $request)
    {
        $records = $request->user()
            ->inferenceRecords()
            ->latest()
            ->limit(20)
            ->get(['id', 'created_at', 'input_text', 'generated_text', 'predicted_label', 'predicted_probability', 'emotion_label', 'emotion_probability']);

        return response()->json([
            'ok' => true,
            'history' => $records,
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
