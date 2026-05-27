<?php

namespace App\Http\Controllers;

use App\Models\InferenceRecord;
use App\Models\MoodJournal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    /** UTF-8 BOM — makes Excel on Windows render accented characters correctly. */
    private const BOM = "\xEF\xBB\xBF";

    /**
     * GET /api/inference/export
     * Descarga el historial de inferencias como CSV.
     * Requiere plan Pro o Plus (feature: historial).
     */
    public function inferenceHistory(Request $request): Response
    {
        $user     = $request->user();
        $features = $user->features();

        abort_unless($features['historial'] ?? false, 403, 'Requiere plan Pro o Plus');

        // NOTE: column names match the `inference_records` table schema.
        // Do NOT use aliases like 'texto', 'etiqueta', 'probabilidad_ansiedad' —
        // those are the FastAPI response keys, not the DB column names.
        $records = InferenceRecord::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get([
                'created_at',
                'input_text',
                'predicted_label',
                'predicted_probability',
                'emotion_label',
                'transcription_source',
            ]);

        $csv  = self::BOM;
        $csv .= "Fecha,Texto ingresado,Etiqueta ansiedad,Probabilidad ansiedad (%),Emoción,Fuente de transcripción\n";

        foreach ($records as $r) {
            $csv .= implode(',', [
                '"' . $r->created_at->format('Y-m-d H:i') . '"',
                '"' . str_replace('"', '""', $r->input_text ?? '') . '"',
                '"' . ($r->predicted_label ?? '') . '"',
                round(($r->predicted_probability ?? 0) * 100, 1),
                '"' . ($r->emotion_label ?? '') . '"',
                '"' . ($r->transcription_source ?? 'manual') . '"',
            ]) . "\n";
        }

        $filename = 'mindra_historial_' . now()->format('Ymd') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /api/journal/export
     * Descarga el diario emocional como CSV.
     */
    public function moodJournal(Request $request): Response
    {
        $user = $request->user();

        $entries = MoodJournal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['created_at', 'mood_score', 'mood_emoji', 'mood_label', 'note', 'tags']);

        $csv  = self::BOM;
        $csv .= "Fecha,Puntuación (1-5),Emoji,Estado de ánimo,Nota,Etiquetas\n";

        foreach ($entries as $e) {
            $csv .= implode(',', [
                '"' . $e->created_at->format('Y-m-d H:i') . '"',
                $e->mood_score,
                '"' . ($e->mood_emoji ?? '') . '"',
                '"' . ($e->mood_label ?? '') . '"',
                '"' . str_replace('"', '""', $e->note ?? '') . '"',
                '"' . implode('; ', $e->tags ?? []) . '"',
            ]) . "\n";
        }

        $filename = 'mindra_diario_' . now()->format('Ymd') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
