<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $response = Illuminate\Support\Facades\Http::post('http://127.0.0.1:8001/api/v1/audit/url', [
        'url' => 'https://example.com'
    ]);
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
