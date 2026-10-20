<?php

return [

    'name' => env('APP_NAME', 'Radar de Compras Públicas'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost:8080'),

    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),

    'locale' => env('APP_LOCALE', 'pt_BR'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'pt_BR'),

    'faker_locale' => 'pt_BR',

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY', 'base64:WbE9s0kK2vGf8M2xT1bN8Q1Z4q5w8r9y0u1i2o3p4a5='),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => 'file',
    ],

];
