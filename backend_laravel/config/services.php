<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Microservicio Python ML (FastAPI) — reemplaza Django
    |--------------------------------------------------------------------------
    | MINDRABACK_URL apunta al microservicio FastAPI (antes apuntaba a Django).
    | En producción: URL interna del VPS, e.g. http://localhost:8001
    | En desarrollo: http://localhost:8001
    */
    'mindraback' => [
        'url'             => env('MINDRABACK_URL', 'http://localhost:8001'),
        'timeout'         => env('MINDRABACK_TIMEOUT', 60),
        'connect_timeout' => env('MINDRABACK_CONNECT_TIMEOUT', 8),
    ],

    'mercadopago' => [
        'access_token'   => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key'     => env('MERCADOPAGO_PUBLIC_KEY'),
        // Obtener desde: Panel MP → Configuración → Notificaciones → Secret
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM v1 API)
    |--------------------------------------------------------------------------
    | FCM_PROJECT_ID: ID del proyecto Firebase (ej. "mindra-pro-xxxxx")
    | FCM_CREDENTIALS_PATH: ruta absoluta al JSON de cuenta de servicio Firebase
    |
    | Si FCM_PROJECT_ID está vacío, las notificaciones push se desactivan
    | silenciosamente (feature flag).
    */
    'fcm' => [
        'project_id'       => env('FCM_PROJECT_ID', ''),
        'credentials_path' => env('FCM_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    ],

];
