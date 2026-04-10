<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GDPR Audit Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e3a8a;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
        .info-row {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        .info-value {
            color: #111827;
        }
        .score-container {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .score-title {
            font-size: 14px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .score-value {
            font-size: 48px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }
        .risk-low { background-color: #d1fae5; color: #059669; }
        .risk-medium { background-color: #fef3c7; color: #d97706; }
        .risk-high { background-color: #fee2e2; color: #dc2626; }
        .risk-critical { background-color: #fecaca; color: #991b1b; }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
            margin-top: 40px;
            margin-bottom: 15px;
        }
        .summary {
            background-color: #f9fafb;
            padding: 15px;
            border-left: 4px solid #93c5fd;
            color: #374151;
            font-size: 15px;
        }
        .clauses-list {
            margin: 0;
            padding-left: 20px;
        }
        .clauses-list li {
            margin-bottom: 12px;
            color: #374151;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">PrivacyGuard AI</div>
        <div class="subtitle">Automated GDPR Compliance Audit</div>
    </div>

    <div class="info-row">
        <span class="info-label">Audited URL:</span>
        <span class="info-value">{{ $url }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date Generated:</span>
        <span class="info-value">{{ date('Y-m-d H:i:s') }}</span>
    </div>

    <div class="score-container">
        <div class="score-title">Overall Compliance Score</div>
        <div class="score-value">{{ $score }}/100</div>
        
        @php
            $riskClass = 'risk-gray';
            $riskLower = strtolower($risk);
            if($riskLower == 'low') $riskClass = 'risk-low';
            if($riskLower == 'medium') $riskClass = 'risk-medium';
            if($riskLower == 'high') $riskClass = 'risk-high';
            if($riskLower == 'critical') $riskClass = 'risk-critical';
        @endphp
        
        <div style="margin-top:15px;">
            <span class="info-label">Risk Level:</span> 
            <span class="risk-badge {{ $riskClass }}">{{ strtoupper($risk) }}</span>
        </div>
    </div>

    <div class="section-title">Executive Summary</div>
    <div class="summary">
        {{ $summary }}
    </div>

    <div class="section-title">Identified Vulnerabilities & Missing Clauses</div>
    @if(isset($missing_clauses) && is_array($missing_clauses) && count($missing_clauses) > 0)
        <ul class="clauses-list">
            @foreach($missing_clauses as $clause)
                <li>{{ $clause }}</li>
            @endforeach
        </ul>
    @else
        <div class="summary" style="border-left-color: #10b981; background-color: #ecfdf5;">
            No missing clauses or vulnerabilities detected. The policy appears fully compliant with standard GDPR requirements.
        </div>
    @endif

    <div class="footer">
        This report was generated automatically by PrivacyGuard AI. <br>
        It is intended for informational purposes only and does not constitute formal legal advice.
    </div>

</body>
</html>