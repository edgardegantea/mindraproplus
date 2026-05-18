<?php

namespace App\Services;

use App\Models\InferenceRecord;
use App\Models\VisitorSession;
use App\Models\Plan;
use App\Services\AI\MindrabackClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class InferenceService
{
    protected MindrabackClient $mindraback;

    public function __construct(MindrabackClient $mindraback)
    {
        $this->mindraback = $mindraback;
    }

    public function predict(?User $user = null, ?UploadedFile $audio = null, string $text = '', ?UploadedFile $image = null, ?float $durationSeconds = null, ?string $facialEmotion = null, ?float $facialConfidence = null): array
    {
        $plan = $this->resolvePlan($user);

        if ($plan->slug === Plan::FREE && $image && !$facialEmotion) {
            return ['ok' => false, 'error' => 'El plan Free no admite análisis de emociones por imagen.'];
        }

        if (!$audio && trim($text) === '') {
            return ['ok' => false, 'error' => 'Debes enviar al menos texto o audio.'];
        }

        $result = $this->mindraback->predict($audio, $text);

        if (!$result['ok']) {
            return $result;
        }

        return DB::transaction(function () use ($user, $audio, $text, $durationSeconds, $image, $plan, $result, $facialEmotion, $facialConfidence) {
            $visitorSession = $this->createOrUpdateVisitorSession($user);

            $audioFilename = null;
            $audioSize = null;

            if ($audio) {
                $audioFilename = $audio->store('inference_audios', 'local');
                $audioSize = $audio->getSize();
            }

            if ($image && $facialEmotion) {
                $image->store('facial_snapshots', 'local');
            }

            $record = InferenceRecord::create([
                'visitor_session_id' => $visitorSession->id,
                'user_id' => $user?->id,
                'audio_filename' => $audioFilename,
                'audio_size_bytes' => $audioSize,
                'audio_duration_seconds' => $durationSeconds,
                'input_text' => $text,
                'generated_text' => $result['texto'] ?? '',
                'transcription_language' => $result['language'] ?? '',
                'transcription_source' => $result['transcription_source'] ?? 'manual',
                'predicted_label' => $result['etiqueta'] ?? '',
                'predicted_probability' => $result['probabilidad_ansiedad'] ?? null,
                'model_name' => $result['model_name'] ?? '',
                'emotion_label' => $facialEmotion ?? $result['emotion_label'] ?? null,
                'emotion_probability' => $facialConfidence ?? $result['emotion_probability'] ?? null,
                'notes' => [
                    'plan'         => $plan->slug,
                    'feature_set'  => $plan->features,
                    'bot_response' => $result['bot_response'] ?? null,
                ],
            ]);

            return [
                'ok' => true,
                'record' => $record,
                'texto' => $record->generated_text,
                'etiqueta' => $record->predicted_label,
                'probabilidad_ansiedad' => $record->predicted_probability,
                'emotion_label' => $record->emotion_label,
                'emotion_probability' => $record->emotion_probability,
                'bot_response' => $result['bot_response'] ?? null,
                'plan' => $plan->slug,
            ];
        });
    }

    public function resolvePlan(?User $user): Plan
    {
        if (!$user) {
            return Plan::free();
        }

        return $user->activePlan() ?? Plan::free();
    }

    protected function createOrUpdateVisitorSession(?User $user): VisitorSession
    {
        return VisitorSession::firstOrCreate(
            ['session_key' => $this->makeVisitorKey($user)],
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => $user?->id,
            ]
        );
    }

    protected function makeVisitorKey(?User $user): string
    {
        $ip = request()->ip() ?? 'unknown';
        $agent = request()->userAgent() ?? 'unknown';
        $identifier = $user?->id ? "user:{$user->id}" : "guest:{$ip}";

        return md5($identifier . '|' . $ip . '|' . $agent);
    }
}
