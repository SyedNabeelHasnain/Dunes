<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report['entity'] ?? 'Executive Report' }} - Dunes Discovery Tourism</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c99732;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
            background: #ffffff;
            font-size: 11px;
            line-height: 1.4;
            padding: 24px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--text-dark);
            margin-bottom: 16px;
        }
        .logo-section h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .logo-section .sub {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .report-meta {
            text-align: right;
            font-size: 10px;
            color: var(--text-muted);
        }
        .report-meta .title {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filters-banner {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            background: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 11px;
        }
        .filter-item {
            display: flex;
            gap: 4px;
        }
        .filter-item strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        .filter-item span {
            color: var(--text-muted);
        }
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .kpi-card {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 12px;
            background: #ffffff;
        }
        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-dark);
            font-family: 'JetBrains Mono', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid var(--border);
            color: #1e293b;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            margin: -24px -24px 24px -24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .no-print-bar button {
            background: var(--primary);
            color: #0f172a;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .no-print-bar button:hover {
            opacity: 0.9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: 9.5px;
        }
        @media print {
            body {
                padding: 10mm;
                font-size: 9px;
            }
            .no-print-bar {
                display: none !important;
            }
            th {
                background: #1e293b !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr:nth-child(even) td {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: landscape;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong>Executive Reporting Preview</strong> &mdash; Generated for Dunes Discovery Tourism LLC
        </div>
        <div>
            <button onclick="window.print()">Print / Export PDF</button>
        </div>
    </div>

    <div class="header">
        <div class="logo-section">
            <h1>DUNES DISCOVERY TOURISM LLC</h1>
            <div class="sub">Dubai, United Arab Emirates &bull; DET Tourism License #1430583</div>
        </div>
        <div class="report-meta">
            <div class="title">{{ $report['entity'] ?? 'System Audit Report' }}</div>
            <div>Generated: <strong>{{ now()->timezone('Asia/Dubai')->format('d M Y, h:i A') }} GST</strong></div>
            <div>Generated By: Authorized Administrator (ID: {{ auth()->id() ?? '1' }})</div>
        </div>
    </div>

    <div class="filters-banner">
        <div class="filter-item">
            <strong>Date Horizon:</strong>
            <span>{{ ucwords(str_replace('_', ' ', $datePreset)) }} ({{ $resolvedFrom }} to {{ $resolvedTo }})</span>
        </div>
        <div class="filter-item">
            <strong>Dataset:</strong>
            <span>{{ ucwords(str_replace('_', ' ', $dataset)) }}</span>
        </div>
        @if(!empty($channel))
            <div class="filter-item">
                <strong>Attribution Channel:</strong>
                <span>{{ $channel }}</span>
            </div>
        @endif
        @if(!empty($status))
            <div class="filter-item">
                <strong>Status Filter:</strong>
                <span>{{ ucfirst($status) }}</span>
            </div>
        @endif
        <div class="filter-item" style="margin-left: auto;">
            <strong>Total Records:</strong>
            <span class="mono">{{ number_format($report['summary']['count'] ?? count($report['rows'])) }}</span>
        </div>
    </div>

    @if(!empty($report['summary']))
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-label">Total Volume</div>
                <div class="kpi-value">{{ number_format($report['summary']['count'] ?? count($report['rows'])) }} <span style="font-size: 11px; font-weight: normal; color: var(--text-muted)">records</span></div>
            </div>
            @if(isset($report['summary']['total_value']))
                <div class="kpi-card">
                    <div class="kpi-label">Total Value (AED)</div>
                    <div class="kpi-value">{{ number_format($report['summary']['total_value'], 2) }}</div>
                </div>
            @endif
            @if(isset($report['summary']['paid_value']))
                <div class="kpi-card">
                    <div class="kpi-label">Paid / Collected (AED)</div>
                    <div class="kpi-value" style="color: #059669;">{{ number_format($report['summary']['paid_value'], 2) }}</div>
                </div>
            @endif
            @if(isset($report['summary']['avg_order_value']))
                <div class="kpi-card">
                    <div class="kpi-label">Avg Order Value (AED)</div>
                    <div class="kpi-value">{{ number_format($report['summary']['avg_order_value'], 2) }}</div>
                </div>
            @endif
            @if(isset($report['summary']['contactable_rate']))
                <div class="kpi-card">
                    <div class="kpi-label">Recoverable Contact Rate</div>
                    <div class="kpi-value" style="color: #2563eb;">{{ $report['summary']['contactable_rate'] }}%</div>
                </div>
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                @foreach($report['columns'] as $colKey => $colLabel)
                    <th>{{ $colLabel }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $index => $row)
                <tr>
                    <td class="mono" style="color: var(--text-muted);">{{ $index + 1 }}</td>
                    @foreach($report['columns'] as $colKey => $colLabel)
                        @php
                            $val = $row[$colKey] ?? '-';
                            $isMoney = str_contains($colKey, 'aed') || str_contains($colKey, 'total') || str_contains($colKey, 'price') || str_contains($colKey, 'revenue');
                            $isDate = str_contains($colKey, 'date') || str_contains($colKey, 'created') || str_contains($colKey, 'time');
                        @endphp
                        <td class="{{ $isMoney ? 'text-right mono' : ($isDate ? 'mono' : '') }}">
                            @if($colKey === 'traffic_channel')
                                <strong>{{ $val ?: 'Direct' }}</strong>
                            @else
                                {{ $val }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['columns']) + 1 }}" style="text-align: center; padding: 24px; color: var(--text-muted);">
                        No records matched the selected criteria and date horizon.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>
            <strong>DUNES DISCOVERY TOURISM LLC</strong> &bull; Executive Business Intelligence & Audit Trail
        </div>
        <div>
            Confidential &bull; Proprietary &bull; Page 1 of 1
        </div>
    </div>
</body>
</html>
