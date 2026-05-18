<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class MindrabackClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.mindraback.url');
    }

    public function predict(?UploadedFile $audio, string $text = '', ?UploadedFile $image = null): array
    {
        $request = Http::withHeaders([
            'Accept' => 'application/json',
        ]);

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

        if ($image) {
            $request = $request->attach(
                'image',
                fopen($image->getRealPath(), 'rb'),
                $image->getClientOriginalName() ?: 'image.jpg'
            );
        }

        $data = array_filter(['texto' => $text]);

        if (!$audio && !$image) {
            $request = $request->asForm();
        }

        try {
            $response = $request->timeout(15)->post("{$this->baseUrl}/inference/predict/", $data);
        } catch (ConnectionException $e) {
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de IA. Intenta más tarde.'];
        }

        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['error'] ?? $body['detail'] ?? $body['message'] ?? null;
            return [
                'ok'    => false,
                'error' => $msg ?: 'Error al procesar la solicitud en el servidor de IA (HTTP ' . $response->status() . ').',
            ];
        }

        return $response->json();
    }

    public function stats(): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/inference/stats/");
        } catch (ConnectionException $e) {
            return ['ok' => false, 'error' => 'No se pudo conectar con el servidor de IA. Intenta más tarde.'];
        }

        if ($response->failed()) {
            return [
                'ok' => false,
                'error' => 'Error en cliente Mindraback al obtener estadísticas',
                'status' => $response->status(),
                'raw_output' => $response->body(),
            ];
        }

        return $response->json();
    }
}
