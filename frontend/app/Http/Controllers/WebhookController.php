<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\AuditReport;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleRazorpayWebhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        $webhookSignature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            // Verify webhook signature
            $api->utility->verifyWebhookSignature($payload, $webhookSignature, $webhookSecret);
        } catch (\Exception $e) {
            Log::error('Razorpay Webhook Signature Verification Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        
        // We handle payment.captured event
        if (isset($data['event']) && $data['event'] === 'payment.captured') {
            $paymentEntity = $data['payload']['payment']['entity'];
            
            $orderId = $paymentEntity['order_id'] ?? null;
            $paymentId = $paymentEntity['id'] ?? null;

            if ($orderId) {
                $report = AuditReport::where('razorpay_order_id', $orderId)->first();
                if ($report) {
                    $report->update([
                        'is_paid' => true,
                        'razorpay_payment_id' => $paymentId
                    ]);
                    Log::info('AuditReport ' . $report->id . ' marked as paid via webhook.');
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}