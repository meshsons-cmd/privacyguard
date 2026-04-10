<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:audit_reports,id',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        // $29 = 2900 cents
        $amountInCents = 2900;
        
        $orderData = [
            'receipt'         => 'rcptid_' . time(),
            'amount'          => $amountInCents, // 2900 cents = $29
            'currency'        => 'USD', // USD or EUR for international clients
            'payment_capture' => 1, // auto capture
            'notes'           => [
                'user_id'   => auth()->id() ?? 'guest',
                'report_id' => $request->report_id,
            ]
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);
            
            // Save the order ID to the report
            $report = \App\Models\AuditReport::find($request->report_id);
            $report->update(['razorpay_order_id' => $razorpayOrder['id']]);

            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'key' => config('services.razorpay.key'),
                'name' => auth()->user()->name ?? 'Guest User',
                'email' => auth()->user()->email ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Order Creation Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create payment order.'], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id'   => 'required',
            'razorpay_signature'  => 'required',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $attributes = [
            'razorpay_order_id'   => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);
            
            // Unlock the "Full GDPR Audit" here
            $report = \App\Models\AuditReport::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($report) {
                $report->update([
                    'is_paid' => true,
                    'razorpay_payment_id' => $request->razorpay_payment_id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful. Full GDPR Audit unlocked.'
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay Signature Verification Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Payment verification failed.'], 400);
        }
    }
}
