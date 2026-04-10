<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPR Audit Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #1e3a8a; /* Tailwind blue-900 */
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        h2 {
            color: #1e40af; /* Tailwind blue-800 */
            margin-top: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-table th, .info-table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background-color: #f8fafc;
            color: #475569;
            width: 30%;
        }
        .risk-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .risk-low { background-color: #059669; }
        .risk-medium { background-color: #d97706; }
        .risk-high { background-color: #e11d48; }
        .risk-critical { background-color: #be123c; }
        
        .summary-box {
            background-color: #f1f5f9;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .clause-list {
            margin-top: 15px;
        }
        .clause-item {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .clause-item:before {
            content: "•";
            color: #ef4444;
            position: absolute;
            left: 0;
            font-size: 1.2em;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.8em;
            color: #64748b;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PrivacyGuard AI - GDPR Audit Report</h1>
        <p>Automated Legal Compliance Analysis</p>
    </div>

    <table class="info-table">
        <tr>
            <th>Website URL</th>
            <td>{{ $url }}</td>
        </tr>
        <tr>
            <th>Audit Date</th>
            <td>{{ $date }}</td>
        </tr>
        <tr>
            <th>Compliance Score</th>
            <td><strong>{{ $score }} / 100</strong></td>
        </tr>
        <tr>
            <th>Risk Level</th>
            <td>
                @php
                    $riskClass = 'risk-low';
                    if (strtolower($risk) == 'medium') $riskClass = 'risk-medium';
                    if (strtolower($risk) == 'high') $riskClass = 'risk-high';
                    if (strtolower($risk) == 'critical') $riskClass = 'risk-critical';
                @endphp
                <span class="risk-badge {{ $riskClass }}">{{ strtoupper($risk) }}</span>
            </td>
        </tr>
    </table>

    <h2>Executive Summary</h2>
    <div class="summary-box">
        <p>{{ $summary }}</p>
    </div>

    <h2>Missing Clauses & Vulnerabilities</h2>
    @if(empty($missing_clauses))
        <p style="color: #059669; font-weight: bold;">✓ No critical missing clauses detected. The policy appears fully compliant.</p>
    @else
        <p>The AI Engine detected the following missing legal clauses or potential vulnerabilities in the privacy policy:</p>
        <div class="clause-list">
            @foreach($missing_clauses as $clause)
                <div class="clause-item">{{ $clause }}</div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by PrivacyGuard AI. It is for informational purposes only and does not constitute formal legal advice.</p>
        <p>&copy; {{ date('Y') }} PrivacyGuard SaaS.</p>
    </div>

</body>
</html>
