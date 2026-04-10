<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$request = Illuminate\Http\Request::create('/', 'GET');
try {
    $response = $app->handleRequest($request);
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
