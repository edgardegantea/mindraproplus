<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->projectId       = config('services.fcm.project_id', '');
        $this->credentialsPath = config(
            'services.fcm.credentials_path',
            storage_path('app/firebase-credentials.json')
        );
    }

    /**
     * Envía una notificación push a todos los tokens registrados del usuario.
     * Devuelve la cantidad de envíos exitosos.
     * Elimina automáticamente los tokens con error 404 (revocados/inválidos).
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        if ($this->projectId === '') {
            return 0;
        }

        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token');

        if ($tokens->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Envía una notificación push a un token específico.
     * Elimina el token si FCM devuelve 404 (token revocado / no registrado).
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if ($this->projectId === '') {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();
            $url         = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            // Convertir todos los valores del array data a string (requisito FCM)
            $stringData = array_map('strval', $data);

            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $stringData,
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::channel('fcm')->info('[FCM] Notificación enviada', [
                    'token'  => substr($token, 0, 20).'...',
                    'title'  => $title,
                ]);
                return true;
            }

            // Token revocado o no registrado → eliminar
            if ($response->status() === 404) {
                DeviceToken::where('token', $token)->delete();
                Log::channel('fcm')->warning('[FCM] Token eliminado (404 — no registrado)', [
                    'token' => substr($token, 0, 20).'...',
                ]);
                return false;
            }

            Log::channel('fcm')->warning('[FCM] Error al enviar notificación', [
                'token'  => substr($token, 0, 20).'...',
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('fcm')->warning('[FCM] Excepción al enviar notificación', [
                'token' => substr($token, 0, 20).'...',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtiene un OAuth 2.0 access token para la API FCM v1.
     * Cacheado 55 minutos (los tokens duran 1 hora).
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 3300, function () {
            $creds = $this->loadCredentials();
            $jwt   = $this->makeJwt($creds);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('[FCM] OAuth token request failed: '.$response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Construye un JWT firmado con RS256 para la autenticación OAuth 2.0.
     */
    private function makeJwt(array $creds): string
    {
        $now = time();

        $header = $this->b64url(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $claims = $this->b64url(json_encode([
            'iss'   => $creds['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = $header.'.'.$claims;

        $privateKey = openssl_pkey_get_private($creds['private_key']);
        if ($privateKey === false) {
            throw new \RuntimeException('[FCM] No se pudo cargar la clave privada de Firebase.');
        }

        $signature = '';
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signingInput.'.'.$this->b64url($signature);
    }

    /**
     * Codificación base64url (sin padding).
     */
    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Carga y valida las credenciales Firebase desde el archivo JSON.
     */
    private function loadCredentials(): array
    {
        if (!file_exists($this->credentialsPath)) {
            throw new \RuntimeException(
                "[FCM] Archivo de credenciales no encontrado: {$this->credentialsPath}"
            );
        }

        $creds = json_decode(file_get_contents($this->credentialsPath), true);

        if (!isset($creds['client_email'], $creds['private_key'])) {
            throw new \RuntimeException(
                '[FCM] Credenciales Firebase inválidas: faltan client_email o private_key.'
            );
        }

        return $creds;
    }
}
