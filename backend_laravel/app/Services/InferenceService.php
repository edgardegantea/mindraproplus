<?php

namespace App\Services;

use App\Models\InferenceRecord;
use App\Models\VisitorSession;
use App\Models\Plan;
use App\Services\AI\MindrabackClient;
use App\Services\BotMemoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;

class InferenceService
{
    protected MindrabackClient $mindraback;
    protected BotMemoryService $botMemory;

    public function __construct(MindrabackClient $mindraback, BotMemoryService $botMemory)
    {
        $this->mindraback = $mindraback;
        $this->botMemory  = $botMemory;
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

        // ── Caché de respuestas ML (solo texto, sin audio/imagen) ────────────
        // Textos idénticos enviados por distintos usuarios dentro de 5 min
        // reutilizan el mismo resultado del modelo → menos carga al ML service.
        $cacheKey    = null;
        $cachedResult = null;

        if (!$audio && !$image && trim($text) !== '') {
            $cacheKey    = 'ml_result:' . md5(mb_strtolower(trim($text)));
            $cachedResult = Cache::get($cacheKey);
        }

        if ($cachedResult) {
            $result = $cachedResult;
            Log::debug('[InferenceService] Resultado obtenido del caché.', ['key' => $cacheKey]);
        } else {
            try {
                // NOTE: $image is feature-gated above but NOT forwarded to FastAPI yet
                // (FastAPI /predict only accepts audio + texto). Future: add /predict image support.
                $result = $this->mindraback->predict($audio, $text);
            } catch (\Exception $e) {
                $result = ['ok' => false];
            }

            if (!$result['ok']) {
                $result = $this->fallbackResult($text);
            } elseif ($cacheKey) {
                // Guardar en caché 5 minutos (solo resultados exitosos del ML real)
                Cache::put($cacheKey, $result, 300);
            }
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

            // Enriquecer la respuesta del bot con contexto de memoria si el usuario está autenticado
            $baseResponse = $result['bot_response'] ?? null;
            $enhancedResponse = $baseResponse;
            if ($user && $baseResponse) {
                $prob    = $record->predicted_probability ?? 0;
                $context = $this->botMemory->buildContext($user, $prob, $text);
                $enhancedResponse = $this->botMemory->enhanceResponse($baseResponse, $context);
            }

            return [
                'ok' => true,
                'record' => $record,
                'texto' => $record->generated_text,
                'etiqueta' => $record->predicted_label,
                'probabilidad_ansiedad' => $record->predicted_probability,
                'emotion_label' => $record->emotion_label,
                'emotion_probability' => $record->emotion_probability,
                'bot_response' => $enhancedResponse,
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

    /**
     * Obtiene o crea la sesión de conversación adecuada.
     *
     * Para usuarios autenticados:
     *   - Reutiliza la sesión más reciente si tuvo actividad en los últimos 30 min.
     *   - Pasado ese umbral se considera una nueva conversación y se abre una sesión nueva.
     *   Esto mejora el chat history: cada "sesión" agrupa mensajes de una conversación
     *   continua, en lugar de acumular todos los mensajes del usuario en un único registro.
     *
     * Para usuarios anónimos:
     *   - Comportamiento original: una sesión fija por ip+agent.
     */
    protected function createOrUpdateVisitorSession(?User $user): VisitorSession
    {
        if ($user) {
            // Buscar sesión reciente (activa en los últimos 30 min)
            $session = VisitorSession::where('user_id', $user->id)
                ->where('updated_at', '>=', now()->subMinutes(30))
                ->latest('updated_at')
                ->first();

            if ($session) {
                $session->touch(); // actualizar updated_at para mantener la ventana
                return $session;
            }

            // Nueva conversación: crear sesión con clave única
            return VisitorSession::create([
                'session_key' => (string) Str::uuid(),
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'user_id'     => $user->id,
            ]);
        }

        // Usuarios anónimos: una sesión persistente por ip+agent
        return VisitorSession::firstOrCreate(
            ['session_key' => $this->makeVisitorKey(null)],
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id'    => null,
            ]
        );
    }

    protected function makeVisitorKey(?User $user): string
    {
        $ip    = request()->ip() ?? 'unknown';
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
     * Respuesta de fallback cuando el servidor ML no está disponible o el
     * circuit breaker está abierto. Analiza palabras clave del texto y
     * elige una respuesta empática de un banco enriquecido de categorías.
     *
     * La respuesta incluye 'fallback' => true para que el cliente pueda
     * informar al usuario que el análisis de IA está en modo limitado.
     */
    protected function fallbackResult(string $text): array
    {
        $t = mb_strtolower($text);

        $responses = [
            // ── Crisis / ideación suicida (prioridad máxima) ──────────────
            'crisis' => [
                'priority' => 10,
                'keywords' => [
                    'suicid', 'quitarme la vida', 'no quiero vivir', 'morir',
                    'hacerme daño', 'matarme', 'ya no puedo más', 'quiero desaparecer',
                ],
                'messages' => [
                    'Lo que describes me preocupa mucho y quiero que sepas que no estás solo/a. Por favor llama ahora al SAPTEL: 55 5259-8121 (disponible 24/7 de forma gratuita). Estoy aquí contigo.',
                    'Gracias por contarme esto — necesito que estés seguro/a. Te pido que llames al 800 290-0024 (LÍNEA DE LA VIDA) ahora mismo. ¿Hay alguien de confianza cerca de ti en este momento?',
                    'Estoy muy presente contigo en este momento. Lo que sientes es real y merece atención urgente. Llama al 911 o al SAPTEL 55 5259-8121. No tienes que pasar por esto solo/a.',
                ],
                'prob' => [0.82, 0.92],
            ],

            // ── Pánico / ataque de pánico ─────────────────────────────────
            'panico' => [
                'priority' => 9,
                'keywords' => [
                    'pánico', 'panico', 'ataque', 'corazón acelerado', 'no puedo respirar',
                    'me voy a desmayar', 'me voy a morir', 'taquicardia', 'temblando',
                    'tiemblo', 'temblar',
                ],
                'messages' => [
                    'Parece que estás teniendo un ataque de pánico. Recuerda: no es peligroso, pasará. Enfoca tu vista en un objeto cercano y nómbralo. Respira: inhala 4 tiempos, sostén 4, exhala 6. ¿Puedes intentarlo?',
                    'Sé que sientes mucho miedo ahora mismo, pero tu cuerpo está a salvo. Pon los pies firmes en el suelo. Nombra 5 cosas que puedes ver, 4 que puedes tocar. Sigo aquí contigo.',
                    'Los ataques de pánico son muy intensos pero temporales — generalmente duran menos de 10 minutos. Respira lento. Inhala por la nariz contando hasta 4, exhala por la boca contando hasta 6. Te acompaño.',
                ],
                'prob' => [0.72, 0.88],
            ],

            // ── Ansiedad general ──────────────────────────────────────────
            'ansiedad' => [
                'priority' => 8,
                'keywords' => [
                    'ansios', 'ansiedad', 'nervios', 'nervioso', 'nerviosa',
                    'angustia', 'angustiado', 'preocupado', 'preocupada', 'preocupacion',
                    'preocupación', 'inquieto', 'inquieta',
                ],
                'messages' => [
                    'Entiendo que estás sintiendo ansiedad. Es una respuesta natural de tu cuerpo ante situaciones difíciles. ¿Quieres que practiquemos juntos una técnica de respiración para calmarte?',
                    'La ansiedad puede sentirse abrumadora, pero recuerda que es temporal. Intenta tres respiraciones profundas: inhala 4 tiempos, sostén 4, exhala 6. ¿Cómo te sientes después?',
                    'Lo que sientes es válido. La ansiedad es el sistema de alarma de tu cuerpo trabajando de más. Estoy aquí contigo. ¿Qué es lo que más te preocupa en este momento?',
                    'Cuando la ansiedad aparece, a veces ayuda preguntarse: ¿esto que temo es probable que ocurra realmente? ¿Y si ocurre, podré manejarlo? Cuéntame qué escenario te genera más inquietud.',
                ],
                'prob' => [0.55, 0.74],
            ],

            // ── Miedo / temor ─────────────────────────────────────────────
            'miedo' => [
                'priority' => 7,
                'keywords' => [
                    'miedo', 'temor', 'aterrad', 'asustado', 'asustada', 'aterrorizado',
                    'fobia', 'horror', 'amenaza', 'peligro',
                ],
                'messages' => [
                    'El miedo es una emoción muy poderosa. Es normal sentirlo, pero no tienes que dejar que te paralice. ¿Qué es específicamente lo que te da miedo? Hablarlo ya ayuda a reducirlo.',
                    'Cuando el miedo aparece, nuestro cuerpo reacciona como si hubiera un peligro real. ¿Puedes describir qué te está causando esa sensación? Quiero entenderte mejor.',
                    'Sentir miedo no significa que algo malo vaya a pasar. A veces nuestra mente amplifica los riesgos. ¿Qué necesitarías para sentirte un poco más seguro/a ahora mismo?',
                ],
                'prob' => [0.52, 0.68],
            ],

            // ── Tristeza / depresión ──────────────────────────────────────
            'triste' => [
                'priority' => 6,
                'keywords' => [
                    'triste', 'tristeza', 'llorar', 'llorando', 'lloro',
                    'deprimid', 'depresion', 'depresión', 'horrible', 'terrible',
                    'vacío', 'vacio', 'sin ganas',
                ],
                'messages' => [
                    'Siento mucho que estés pasando por esto. Está bien no estar bien. ¿Hay algo específico que te está pesando hoy?',
                    'La tristeza a veces llega sin aviso y nos pesa mucho. No tienes que enfrentarla solo/a. Cuéntame un poco más, ¿qué pasó?',
                    'Es completamente válido sentirse así. Estoy aquí para escucharte sin juzgarte. ¿Quieres contarme qué está pasando?',
                    'Cuando la tristeza es muy profunda, puede ser señal de que necesitamos apoyo adicional. ¿Has podido hablar con alguien de confianza sobre cómo te sientes?',
                ],
                'prob' => [0.45, 0.60],
            ],

            // ── Soledad ───────────────────────────────────────────────────
            'solo' => [
                'priority' => 6,
                'keywords' => [
                    'solo', 'sola', 'soledad', 'nadie me entiende', 'no tengo a nadie',
                    'aislado', 'aislada', 'incomunicado', 'abandonado', 'abandonada',
                ],
                'messages' => [
                    'Sentirse solo/a puede ser una de las experiencias más dolorosas. Quiero que sepas que en este momento estoy aquí contigo. ¿Hay alguien en tu vida con quien puedas conectar hoy?',
                    'La soledad duele, y es completamente válido sentirla. Muchas personas la experimentan aunque no lo expresen. ¿Qué tipo de conexión te hace falta en este momento?',
                    'Gracias por contarme cómo te sientes. Aunque pueda parecer que nadie entiende, tu experiencia importa. ¿Qué ha cambiado últimamente en tus relaciones?',
                ],
                'prob' => [0.40, 0.55],
            ],

            // ── Estrés / agotamiento ──────────────────────────────────────
            'estres' => [
                'priority' => 5,
                'keywords' => [
                    'estres', 'estrés', 'estresad', 'agobiad', 'agobi',
                    'trabajo', 'ocupad', 'deadlines', 'plazos', 'presion', 'presión',
                    'responsabilidades', 'sobrecargado', 'sobrecargada',
                ],
                'messages' => [
                    'El estrés acumulado puede agotarnos profundamente. Es importante darte un momento para respirar. ¿Cuándo fue la última vez que descansaste de verdad?',
                    'Entiendo que estás bajo mucha presión. A veces ayuda priorizar: ¿qué es lo más urgente en este momento? Vamos paso a paso, no tienes que resolver todo hoy.',
                    'El cuerpo nos avisa cuando llegamos al límite. Tómate un momento ahora: cierra los ojos, respira hondo, y recuerda que eres capaz de manejar esto.',
                    'Cuando hay demasiadas cosas a la vez, el cerebro se satura. ¿Podrías escribir en papel las 3 cosas más importantes del día y dejar el resto para después?',
                ],
                'prob' => [0.50, 0.68],
            ],

            // ── Cansancio / agotamiento físico-emocional ──────────────────
            'cansado' => [
                'priority' => 4,
                'keywords' => [
                    'cansad', 'agotad', 'exhaust', 'sin energía', 'sin energia',
                    'no duermo', 'no puedo dormir', 'insomnio', 'rendido', 'rendida',
                ],
                'messages' => [
                    'El agotamiento físico y emocional van de la mano. Tu cuerpo te está pidiendo descanso. ¿Cuántas horas has dormido esta semana en promedio?',
                    'Sentirse sin energía puede afectar cómo percibimos todo lo demás. ¿Hay algo que esté robándote el sueño o el descanso últimamente?',
                    'El cansancio profundo a veces es la forma en que el cuerpo dice "necesito parar". ¿Hay algo que puedas delegar o posponer para darte un respiro hoy?',
                ],
                'prob' => [0.38, 0.52],
            ],

            // ── Frustración / enojo ───────────────────────────────────────
            'frustrado' => [
                'priority' => 4,
                'keywords' => [
                    'frustrad', 'frustración', 'frustracion', 'enojado', 'enojada',
                    'molesto', 'molesta', 'hartado', 'hartada', 'harto', 'harta',
                    'coraje', 'rabia', 'enojo', 'impotencia',
                ],
                'messages' => [
                    'La frustración es una emoción completamente válida. Puede ser muy agotadora. ¿Qué situación es la que más te está generando ese sentimiento?',
                    'Cuando las cosas no salen como esperamos, el enojo y la frustración son reacciones naturales. ¿Qué necesitarías para que la situación mejorara?',
                    'Entiendo esa sensación de impotencia cuando algo no avanza como quisieras. A veces ayuda separar lo que podemos controlar de lo que no. ¿Qué parte de esto sí está en tus manos?',
                ],
                'prob' => [0.42, 0.58],
            ],

            // ── Confusión / desorientación ────────────────────────────────
            'confuso' => [
                'priority' => 3,
                'keywords' => [
                    'confundido', 'confundida', 'confusión', 'confusion', 'perdido',
                    'perdida', 'no sé qué hacer', 'no sé que hacer', 'no entiendo',
                    'desorientado', 'desorientada', 'sin rumbo',
                ],
                'messages' => [
                    'Sentirse perdido/a puede generar mucha angustia. A veces necesitamos un momento de pausa antes de poder ver el camino con claridad. ¿Qué área de tu vida es la que más sientes que necesita dirección?',
                    'La confusión muchas veces aparece cuando tenemos demasiadas opciones o cuando estamos en transición. ¿Puedes contarme más sobre qué es lo que te genera esa sensación?',
                    'No tener claridad no significa que estés haciendo algo mal — a veces simplemente necesitamos más información o tiempo. ¿Qué pregunta es la que más te ronda en este momento?',
                ],
                'prob' => [0.30, 0.45],
            ],

            // ── Bienestar / positivo ──────────────────────────────────────
            'bien' => [
                'priority' => 2,
                'keywords' => [
                    'bien', 'mejor', 'contento', 'contenta', 'feliz', 'alegre',
                    'tranquilo', 'tranquila', 'calm', 'descansado', 'descansada',
                    'motivado', 'motivada', 'energía', 'energia',
                ],
                'messages' => [
                    '¡Me alegra escuchar eso! Mantener ese estado de bienestar es muy importante. ¿Hay algo que quieras explorar o trabajar hoy?',
                    'Qué bueno saberlo. Los momentos de calma son valiosos — aprovéchalos para recargar energía. ¿Hay algo en lo que pueda ayudarte hoy?',
                    'Me da gusto que te sientas así. ¿Quieres hacer un check-in rápido de bienestar o hay algo específico que quieras conversar?',
                    'Cuando estamos bien es un buen momento para reflexionar: ¿qué cosas de tu rutina están contribuyendo a ese bienestar? Identificarlas ayuda a mantenerlas.',
                ],
                'prob' => [0.08, 0.28],
            ],
        ];

        // ── Detectar categoría por prioridad + palabras clave ────────────────
        $matched  = null;
        $maxPrio  = -1;
        foreach ($responses as $category => $data) {
            if ($data['priority'] <= $maxPrio) continue;
            foreach ($data['keywords'] as $kw) {
                if (str_contains($t, $kw)) {
                    $matched  = $category;
                    $maxPrio  = $data['priority'];
                    break;
                }
            }
        }

        // ── Respuestas generales si no hay coincidencia ───────────────────────
        $general = [
            'Gracias por compartir eso conmigo. Estoy aquí para escucharte. ¿Puedes contarme un poco más sobre cómo te sientes?',
            'Te escucho. Cada emoción que sientes es válida e importante. ¿Qué más está pasando por tu mente ahora mismo?',
            'Aprecio que estés aquí y que confíes en mí. ¿Hay algo en particular en lo que te pueda ayudar hoy?',
            'Estoy presente contigo en este momento. ¿Cómo describirías tu estado emocional ahora mismo, del 1 al 10?',
            'Compartir lo que sentimos ya es un paso importante. ¿Qué te trajo hoy a hablar con Mindra?',
        ];

        $pool        = $matched ? $responses[$matched]['messages'] : $general;
        $botResponse = $pool[array_rand($pool)];

        // ── Probabilidad de ansiedad basada en categoría ──────────────────────
        if ($matched && isset($responses[$matched]['prob'])) {
            [$min, $max] = $responses[$matched]['prob'];
            $prob = round($min + mt_rand(0, (int)(($max - $min) * 100)) / 100, 2);
        } else {
            $prob = round(0.25 + mt_rand(0, 25) / 100, 2);
        }

        $etiqueta = match(true) {
            $prob >= 0.75 => 'Ansiedad elevada detectada',
            $prob >= 0.50 => 'Posibles indicadores de ansiedad',
            $prob >= 0.30 => 'Leve activación emocional',
            default       => 'Sin indicadores fuertes',
        };

        Log::info('[InferenceService] Fallback activado.', [
            'matched_category' => $matched ?? 'general',
            'prob'             => $prob,
            'text_snippet'     => mb_substr($text, 0, 60),
        ]);

        return [
            'ok'                    => true,
            'fallback'              => true,
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
