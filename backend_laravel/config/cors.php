<?php

return [
    /*
     * Rutas que aplican CORS. api/* cubre todos los endpoints de la API.
     * El webhook de MercadoPago y la página /app-callback son web, no API.
     */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    /*
     * La API usa autenticación por Bearer token (Sanctum), NO por cookies.
     * Con tokens no se necesitan credenciales CORS → allowed_origins puede
     * ser '*', lo que simplifica el soporte para web, Postman, apps móviles
     * y cualquier origen (Flutter web local, producción, staging, etc.).
     */
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    /*
     * 7200 s = 2 h. El navegador cachea el preflight este tiempo,
     * reduciendo peticiones OPTIONS repetidas.
     */
    'max_age' => 7200,

    /*
     * DEBE ser false cuando allowed_origins = '*'.
     * La API usa Bearer tokens; las cookies/credenciales de sesión
     * no se necesitan en peticiones cross-origin.
     */
    'supports_credentials' => false,
];
