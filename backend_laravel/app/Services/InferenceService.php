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
        $plan     = $this->resolvePlan($user);
        $features = $user ? $user->features() : ($plan->features ?? []);

        // #6 — imagen: solo plan Plus
        if ($image && empty($features['imagen'])) {
            $image         = null;
            $facialEmotion = null;
            $facialConfidence = null;
        }

        // #4 — multimodal (texto + audio + imagen simultáneos): solo plan Plus
        $hasText  = trim($text) !== '';
        $hasAudio = $audio !== null;
        $hasImage = $image !== null;

        if ($hasText && $hasAudio && $hasImage && empty($features['multimodal'])) {
            // Degradar: ignorar imagen si no tiene multimodal
            $image            = null;
            $facialEmotion    = null;
            $facialConfidence = null;
        }

        if (!$audio && trim($text) === '') {
            return ['ok' => false, 'error' => 'Debes enviar al menos texto o audio.'];
        }

        try {
            // NOTE: $image is feature-gated above but NOT forwarded to FastAPI yet
            // (FastAPI /predict only accepts audio + texto). Future: add /predict image support.
            $result = $this->mindraback->predict($audio, $text);
        } catch (\Exception $e) {
            $result = ['ok' => false];
        }

        if (!$result['ok']) {
            $result = $this->fallbackResult($text);
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

    // ─────────────────────────────────────────────────────────────────────────
    // transcribe — transcripción de audio con Whisper vía microservicio ML
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribe(UploadedFile $audio, string $language = 'es'): array
    {
        $result = $this->mindraback->transcribe($audio, $language);

        if (!($result['ok'] ?? false)) {
            return [
                'ok'    => false,
                'error' => $result['error'] ?? 'Error al transcribir el audio.',
            ];
        }

        return [
            'ok'       => true,
            'text'     => $result['text']     ?? '',
            'language' => $result['language'] ?? $language,
        ];
    }

    /** Alias público para que los controladores web puedan invocar el fallback directamente. */
    public function publicFallback(string $text): array
    {
        return $this->fallbackResult($text);
    }

    /**
     * Respuesta de fallback cuando el servidor de IA no está disponible.
     * Analiza palabras clave del texto para elegir una respuesta empática.
     */
    protected function fallbackResult(string $text): array
    {
        $t = mb_strtolower($text);

        $responses = [
            'ansiedad' => [
                'keywords' => ['ansios', 'ansiedad', 'nervios', 'nervioso', 'nerviosa', 'angustia', 'angustiado', 'pánico', 'panico', 'miedo', 'temor'],
                'messages' => [
                    'Entiendo que estás sintiendo ansiedad. Es una respuesta natural de tu cuerpo ante situaciones difíciles. ¿Quieres que practiquemos juntos una técnica de respiración para calmarte?',
                    'La ansiedad puede sentirse abrumadora, pero recuerda que es temporal. Intenta hacer tres respiraciones profundas: inhala en 4 tiempos, sostén 4, exhala en 6. ¿Cómo te sientes ahora?',
                    'Lo que sientes es válido. La ansiedad es el sistema de alarma de tu cuerpo trabajando de más. Estoy aquí contigo. ¿Qué es lo que más te preocupa en este momento?',
                ],
            ],
            'triste' => [
                'keywords' => ['triste', 'tristeza', 'lloran', 'llorar', 'llorando', 'deprimid', 'solo', 'sola', 'soledad', 'mal', 'horrible', 'terrible'],
                'messages' => [
                    'Siento mucho que estés pasando por esto. Está bien no estar bien. ¿Hay algo específico que te está pesando hoy?',
                    'La tristeza a veces llega sin aviso. No tienes que enfrentarla solo/a. Cuéntame un poco más, ¿qué pasó?',
                    'Es completamente válido sentirse así. Estoy aquí para escucharte sin juzgarte. ¿Quieres contarme qué está pasando?',
                ],
            ],
            'estres' => [
                'keywords' => ['estres', 'estrés', 'estresad', 'agobiad', 'agobi', 'cansad', 'agotad', 'exhaust', 'trabajo', 'ocupad'],
                'messages' => [
                    'El estrés acumulado puede agotarnos. Es importante darte un momento para respirar. ¿Cuándo fue la última vez que descansaste de verdad?',
                    'Entiendo que estás bajo mucha presión. A veces ayuda priorizar: ¿qué es lo más urgente en este momento? Vamos paso a paso.',
                    'El cuerpo nos avisa cuando llegamos al límite. Tomate un momento ahora mismo: cierra los ojos, respira hondo, y recuerda que eres capaz de con esto.',
                ],
            ],
            'bien' => [
                'keywords' => ['bien', 'mejor', 'contento', 'contenta', 'feliz', 'alegre', 'tranquilo', 'tranquila', 'calm'],
                'messages' => [
                    '¡Me alegra escuchar eso! Mantener ese estado de bienestar es muy importante. ¿Hay algo que quieras explorar hoy?',
                    'Qué bueno saberlo. Los momentos de calma son valiosos. ¿Hay algo en lo que pueda ayudarte hoy?',
                    'Me da gusto que te sientas así. ¿Quieres hacer un check-in rápido de bienestar o simplemente conversar?',
                ],
            ],
        ];

        // Detectar categoría por palabras clave
        $matched = null;
        foreach ($responses as $category => $data) {
            foreach ($data['keywords'] as $kw) {
                if (str_contains($t, $kw)) {
                    $matched = $category;
                    break 2;
                }
            }
        }

        // Respuestas generales si no hay coincidencia
        $general = [
            'Gracias por compartir eso conmigo. Estoy aquí para escucharte. ¿Puedes contarme un poco más sobre cómo te sientes?',
            'Te escucho. Cada emoción que sientes es válida e importante. ¿Qué más está pasando por tu mente ahora mismo?',
            'Aprecio que estés aquí y que confíes en mí. ¿Hay algo en particular en lo que te pueda ayudar hoy?',
            'Estoy presente contigo en este momento. ¿Cómo describirías tu estado emocional ahora mismo, del 1 al 10?',
            'Compartir lo que sentimos ya es un paso importante. ¿Qué te trajo hoy a hablar con Mindra?',
        ];

        $pool = $matched ? $responses[$matched]['messages'] : $general;
        $botResponse = $pool[array_rand($pool)];

        // Probabilidad de ansiedad basada en categoría
        $prob = match($matched) {
            'ansiedad' => round(0.55 + (mt_rand(0, 20) / 100), 2),
            'triste'   => round(0.45 + (mt_rand(0, 15) / 100), 2),
            'estres'   => round(0.50 + (mt_rand(0, 20) / 100), 2),
            'bien'     => round(0.10 + (mt_rand(0, 20) / 100), 2),
            default    => round(0.25 + (mt_rand(0, 30) / 100), 2),
        };

        $etiqueta = $prob > 0.5
            ? 'Posibles indicadores de ansiedad'
            : 'Sin indicadores fuertes';

        return [
            'ok'                    => true,
            'etiqueta'              => $etiqueta,
            'probabilidad_ansiedad' => $prob,
            'language'              => 'es',
            'model_name'            => 'fallback_mindra',
            'texto'                 => $text,
            'transcription_source'  => 'fallback',
            'bot_response'          => $botResponse,
        ];
    }
}
