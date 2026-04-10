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
Route::post('/api/scan', [ReportController::class, 'scan'])->name('api.scan');
Route::get('/api/wakeup', [ReportController::class, 'wakeup'])->name('api.wakeup');