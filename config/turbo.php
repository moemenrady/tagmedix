<?php

return [
    'base_url' => env('TURBO_BASE_URL', 'https://platform.turbo.info'),
    'auth_key' => env('TURBO_AUTH_KEY'),
    'client_code' => env('TURBO_CLIENT_CODE'),
    'timeout' => env('TURBO_TIMEOUT', 30),
    'retry' => env('TURBO_RETRY', 3),
    'webhook_secret' => env('TURBO_WEBHOOK_SECRET'),
];
