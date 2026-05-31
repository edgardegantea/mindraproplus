<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry DSN
    |--------------------------------------------------------------------------
    | Data Source Name de tu proyecto Sentry. Si está vacío, Sentry se desactiva
    | silenciosamente (útil para entornos de desarrollo/testing).
    |
    | Obtener desde: sentry.io → tu proyecto → Settings → Client Keys (DSN)
    */
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    | traces_sample_rate: fracción de transacciones que se envían a Sentry
    | (1.0 = 100%, 0.1 = 10%). En producción usar un valor bajo para no
    | exceder la cuota gratuita.
    |
    | profiles_sample_rate: desactivado (0) — requiere extensión Excimer.
    */
    'traces_sample_rate'   => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    'profiles_sample_rate' => 0,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    | Los breadcrumbs proveen contexto adicional para cada error capturado.
    | sql_bindings desactivado para no exponer datos sensibles de usuarios.
    */
    'breadcrumbs' => [
        'logs'         => true,
        'sql_queries'  => true,
        'sql_bindings' => false,
        'queue_info'   => true,
        'command_info' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | PII y metadatos
    |--------------------------------------------------------------------------
    */
    'send_default_pii' => false,
    'environment'      => env('APP_ENV', 'production'),
    'release'          => env('APP_VERSION', '2.1.0'),

];
