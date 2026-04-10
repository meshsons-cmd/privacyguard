<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
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