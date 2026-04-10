<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $response = Illuminate\Support\Facades\Http::withHeaders([
        'X-API-Key' => env('API_SECRET_KEY'),
        'Authorization' => 'Bearer ' . env('API_SECRET_KEY')
    ])->post('http://127.0.0.1:8001/api/v1/audit/url', [
        'url' => 'https://ourastroguruji.com/'
    ]);
    
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
