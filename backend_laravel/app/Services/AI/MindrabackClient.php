<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP hacia el microservicio Python ML (FastAPI).
 *
 * El microservicio corre en PYTHON_ML_SERVICE_URL (default: http://localhost:8001)
 * y expone:  POST /predict   POST /transcribe   GET /health
 *
 * Circuit breaker: si el servicio falla CB_THRESHOLD veces consecutivas,
 * el circuito se "abre" durante CB_OPEN_TTL segundos y las peticiones
 * se rechazan de inmediato (sin esperar el connect_timeout) para que
 * InferenceService pueda activar el fallback sin demora.
 */
class MindrabackClient
{
    protected string $baseUrl;
    protected int    $timeout;
    protected int    $connectTimeout;

    // ── Circuit breaker ──────────────────────────────────────────────────────
    private const CB_FAILURES_KEY = 'ml_circuit_failures';
    private const CB_OPEN_KEY     = 'ml_circuit_open';
    private const CB_THRESHOLD    = 3;    // fallos consecutivos para abrir
    private const CB_OPEN_TTL     = 60;   // segundos que el circuito permanece abierto
    private const CB_FAILURES_TTL = 120;  // ventana de tiempo para contar fallos

    public function __construct()
    {
        $this->baseUrl        = rtrim(config('services.mindraback.url', 'http://localhost:8001'), '/');
        $this->timeout        = (int) config('services.mindraback.timeout', 60);
        $this->connectTimeout = (int) config('services.mindraback.connect_timeout', 8);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Circuit breaker helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function isCircuitOpen(): bool
    {
        return (bool) Cache::get(self::CB_OPEN_KEY, false);
    }

    private function recordFailure(): void
    {
        $failures = (int) Cache::get(self::CB_FAILURES_KEY, 0) + 1;
        Cache::put(self::CB_FAILURES_KEY, $failures, self::CB_FAILURES_TTL);

        if ($failures >= self::CB_THRESHOLD) {
            Cache::put(self::CB_OPEN_KEY, true, self::CB_OPEN_TTL);
            Log::warning('[MindrabackClient] Circuit breaker ABIERTO — microservicio ML no disponible.', [
                'failures'  => $failures,
                'open_for'  => self::CB_OPEN_TTL . 's',
                'base_url'  => $this->baseUrl,
            ]);
        }
    }

    private function recordSuccess(): void
    {
        Cache::forget(self::CB_FAILURES_KEY);
        Cache::forget(self::CB_OPEN_KEY);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // predict — inferencia multimodal de ansiedad
    // ─────────────────────────────────────────────────────────────────────────
    public function predict(?UploadedFile $audio, string $text = ''): array
    {
        // Circuit breaker: si el circuito está abierto, rechazar de inmediato
        if ($this->isCircuitOpen()) {
            Log::info('[MindrabackClient] Circuit abierto — petición rechazada sin intentar conexión.');
            return ['ok' => false, 'error' => 'circuit_open'];
        }

        $request = Http::withHeaders(['Accept' => 'application/json']);

        if ($audio) {
            $mime     = $audio->getMimeType() ?: 'audio/webm';
            $origName = $audio->getClientOriginalName() ?: 'recording.webm';
            $request  = $request->attach(
                'audio',
                fopen($audio->getRealPath(), 'rb'),
                $origName,
                ['Content-Type' => $mime]
            );
        }

        // NOTE: image parameter is intentionally NOT forwarded to FastAPI.
        // The /predict endpoint only accepts audio + texto; image modal is
        // handled at the InferenceService level (feature-gated) and will
        // require a future FastAPI endpoint update to support it end-to-end.

        $data = array_filter(['texto' => $text]);

        if (!$audio) {
            $request = $request->asForm();
        }

        try {
            $response = $request
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->post("{$this->baseUrl}/predict", $data);
        } catch (\Exception $e) {
            $this->recordFailure();
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de IA. Intenta más tarde.'];
        }

        if ($response->failed()) {
            $this->recordFailure();
            $body = $response->json();
            $msg  = $body['error'] ?? $body['detail'] ?? $body['message'] ?? null;
            return [
                'ok'    => false,
                'error' => $msg ?: 'Error en el servidor de IA (HTTP ' . $response->status() . ').',
            ];
        }

        $this->recordSuccess();
        return $response->json();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // transcribe — transcripción de audio con Whisper
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribe(UploadedFile $audio, string $language = 'es'): array
    {
        if ($this->isCircuitOpen()) {
            return ['ok' => false, 'error' => 'circuit_open'];
        }

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->attach(
                    'audio',
                    fopen($audio->getRealPath(), 'rb'),
                    $audio->getClientOriginalName() ?: 'recording.webm',
                    ['Content-Type' => $audio->getMimeType() ?: 'audio/webm']
                )
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->post("{$this->baseUrl}/transcribe", ['language' => $language]);
        } catch (\Exception $e) {
            $this->recordFailure();
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de transcripción.'];
        }

        if ($response->failed()) {
            $this->recordFailure();
            $body = $response->json();
            $msg  = $body['error'] ?? $body['detail'] ?? null;
            return [
                'ok'    => false,
                'error' => $msg ?: 'Error al transcribir el audio (HTTP ' . $response->status() . ').',
            ];
        }

        $this->recordSuccess();
        return $response->json();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // health — verifica que el microservicio ML está vivo
    // ─────────────────────────────────────────────────────────────────────────
    public function health(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->ok()
                ? array_merge(['reachable' => true], $response->json() ?? [])
                : ['reachable' => false, 'status' => $response->status()];
        } catch (\Exception) {
            return ['reachable' => false, 'error' => 'Microservicio ML no disponible.'];
        }
    }
}
