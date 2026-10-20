<?php

return [

    'radar_go' => [
        'base_url' => env('GO_API_URL', 'http://127.0.0.1:8000'),
        'internal_token' => env('GO_INTERNAL_TOKEN', 'radar-token-interno-ultra-seguro-compartilhado-com-painel'),
        'timeout' => (int) env('GO_API_TIMEOUT', 15),
    ],

];
