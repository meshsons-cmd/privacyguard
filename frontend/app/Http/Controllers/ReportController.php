<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function scan(Request $request)
    {
        set_time_limit(180); // Prevent PHP script timeout during long AI operations

        $request->validate([
            'url' => 'required|url'
        ]);

        // Connect the Laravel frontend to the Python API using secure HTTP requests
        // Dynamic Handshake strictly via env('AI_ENGINE_URL') (via config cache)
        $apiUrl = config('services.ai_engine.url');
        $apiKey = config('services.ai_engine.key');
        
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Authorization' => 'Bearer ' . $apiKey
            ])->timeout(180)->post($apiUrl, [
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
            
            $errorMessage = 'The AI Auditor Engine returned an error.';
            if (is_array($errorData)) {
                if (isset($errorData['error'])) {
                    $errorMessage = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
                } elseif (isset($errorData['detail'])) {
                    $errorMessage = is_string($errorData['detail']) ? $errorData['detail'] : json_encode($errorData['detail']);
                }
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $errorData
            ], $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Illuminate\Support\Facades\Log::error('AI Auditor Proxy Timeout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'The AI Auditor Engine is currently waking up from sleep mode or taking too long. Please wait 30 seconds and try again.',
                'debug_error' => $e->getMessage()
            ], 504);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Auditor Proxy Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection to AI Engine failed: ' . $e->getMessage(),
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function wakeup()
    {
        try {
            $apiUrl = config('services.ai_engine.url');
            if (!$apiUrl) {
                return response()->json(['status' => 'no url configured']);
            }
            
            // Base URL to ping for wake up (strip /api/v1/audit/url)
            $baseUrl = str_replace('/api/v1/audit/url', '', $apiUrl);
            
            Http::timeout(5)->get($baseUrl . '/');
            
            return response()->json(['status' => 'waking up']);
        } catch (\Exception $e) {
            // Ignore errors for wakeup
            return response()->json(['status' => 'failed to wake up', 'error' => $e->getMessage()]);
        }
    }

    public function downloadPdf(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|string',
            'score' => 'required|integer',
            'risk' => 'required|string',
            'summary' => 'nullable|string',
            'missing_clauses' => 'nullable|array',
        ]);

        $pdf = Pdf::loadView('pdf.audit', $data);
        
        return $pdf->download('PrivacyGuard_Audit_Report.pdf');
    }
}