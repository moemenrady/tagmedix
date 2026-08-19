<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$turboService = app(\App\Integrations\Turbo\Services\TurboService::class);
$govs = $turboService->getGovernments();
echo json_encode($govs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
