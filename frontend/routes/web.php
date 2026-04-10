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
    // Use env('AI_ENGINE_URL') for dynamic Cloud deployment (Railway/Render)
    $apiUrl = env('AI_ENGINE_URL', 'http://127.0.0.1:8001/api/v1/audit/url');
    
    try {
        $response = Http::withHeaders([
            'X-API-Key' => env('API_SECRET_KEY'),
            'Authorization' => 'Bearer ' . env('API_SECRET_KEY')
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

        return response()->json([
            'message' => 'The AI Auditor Engine returned an error.',
            'details' => $response->json()
        ], $response->status());

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('AI Auditor Proxy Failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Unable to reach the AI Auditor Engine. Please ensure the Python backend is running on port 8001.',
            'debug_error' => $e->getMessage()
        ], 500);
    }
});