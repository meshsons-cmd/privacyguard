<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WebhookController;

// Razorpay Webhook (must be outside auth and CSRF)
Route::post('/api/razorpay/webhook', [WebhookController::class, 'handleRazorpayWebhook'])->name('razorpay.webhook');

// Serve the Dashboard Vue component
Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// Razorpay Checkout Routes
Route::post('/payment/order', [PaymentController::class, 'createOrder'])->name('payment.order');
Route::post('/payment/verify', [PaymentController::class, 'verifyPayment'])->name('payment.verify');

// PDF Export Route
Route::post('/report/download', [ReportController::class, 'downloadPdf'])->name('report.download');

// Proxy route to the Python Backend API
Route::post('/api/scan', function (Request $request) {
    $request->validate([
        'url' => 'required|url'
    ]);

    // Connect the Laravel frontend to the Python API using secure HTTP requests
    $apiUrl = config('services.ai_engine.url');
    $apiKey = config('services.ai_engine.key');
    
    try {
        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey
        ])->timeout(60)->post($apiUrl, [
            'url' => $request->input('url')
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Save report to database
            $report = \App\Models\AuditReport::create([
                'user_id' => auth()->id(), // null if not logged in
                'url' => $request->input('url'),
                'compliance_score' => $data['score'] ?? 0,
                'risk_level' => $data['risk'] ?? 'Unknown',
                'summary' => $data['summary'] ?? '',
                'missing_clauses' => $data['missing_clauses'] ?? [],
                'is_paid' => false,
            ]);
            
            $data['report_id'] = $report->id;
            return response()->json($data);
        }

        $errorData = $response->json();
        $errorMessage = is_array($errorData) && isset($errorData['detail']) 
            ? (is_string($errorData['detail']) ? $errorData['detail'] : json_encode($errorData['detail'])) 
            : 'The AI Auditor Engine returned an error.';

        return response()->json([
            'message' => $errorMessage,
            'details' => $errorData
        ], $response->status());

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('AI Auditor Proxy Failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Connection to AI Engine failed: ' . $e->getMessage(),
            'debug_error' => $e->getMessage()
        ], 500);
    }
});