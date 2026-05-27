<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP hacia el microservicio Python ML (FastAPI).
 *
 * El microservicio corre en PYTHON_ML_SERVICE_URL (default: http://localhost:8001)
 * y expone:  POST /predict   POST /transcribe   GET /health
 */
class MindrabackClient
{
    protected string $baseUrl;
    protected int    $timeout;
    protected int    $connectTimeout;

    public function __construct()
    {
        $this->baseUrl        = rtrim(config('services.mindraback.url', 'http://localhost:8001'), '/');
        $this->timeout        = (int) config('services.mindraback.timeout', 60);
        $this->connectTimeout = (int) config('services.mindraback.connect_timeout', 8);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // predict — inferencia multimodal de ansiedad
    // ─────────────────────────────────────────────────────────────────────────
    public function predict(?UploadedFile $audio, string $text = ''): array
    {
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
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de IA. Intenta más tarde.'];
        }

        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['error'] ?? $body['detail'] ?? $body['message'] ?? null;
            return [
                'ok'    => false,
                'error' => $msg ?: 'Error en el servidor de IA (HTTP ' . $response->status() . ').',
            ];
        }

        return $response->json();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // transcribe — transcripción de audio con Whisper
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribe(UploadedFile $audio, string $language = 'es'): array
    {
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
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de transcripción.'];
        }

        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['error'] ?? $body['detail'] ?? null;
            return [
                'ok'    => false,
                'error' => $msg ?: 'Error al transcribir el audio (HTTP ' . $response->status() . ').',
            ];
        }

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
