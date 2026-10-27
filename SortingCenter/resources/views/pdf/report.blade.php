<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] ?? 'Operations Report' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a2238;
            margin: 20px 25px;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0052cc;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand {
            font-size: 18px;
            font-weight: 800;
            color: #0052cc;
            letter-spacing: -0.5px;
        }
        .sub-brand {
            font-size: 10px;
            color: #6b778c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .report-title {
            font-size: 15px;
            font-weight: 700;
            color: #172b4d;
            margin-top: 8px;
        }
        .meta {
            font-size: 10px;
            color: #5e6c84;
            float: right;
            text-align: right;
        }
        .clearfix {
            clear: both;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-card {
            background-color: #f4f5f7;
            border: 1px solid #dfe1e6;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #5e6c84;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: 800;
            color: #091e42;
        }
        .kpi-sub {
            font-size: 8.5px;
            color: #7a869a;
            margin-top: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #ebecf0;
            color: #172b4d;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 8px;
            border: 1px solid #dfe1e6;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #ebecf0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafbfc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #dfe1e6;
            padding-top: 8px;
            font-size: 8.5px;
            color: #7a869a;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="meta">
            <div><strong>Period:</strong> {{ $report['range_label'] ?? 'All' }}</div>
            <div><strong>Generated:</strong> {{ now()->format('M j, Y h:i A') }}</div>
        </div>
        <div class="brand">MIMOO SORTING CENTER</div>
        <div class="sub-brand">Santa Cruz Hub • Laguna Operations</div>
        <div class="report-title">{{ $report['title'] ?? 'Operations Report' }}</div>
        <div class="clearfix"></div>
    </div>

    <!-- ── 4 Distinct KPIs ── -->
    <table class="kpi-table">
        <tr>
            @foreach ($report['kpis'] ?? [] as $kpi)
                <td class="kpi-card" style="width: 25%;">
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                    <div class="kpi-value">{{ $kpi['value'] }}</div>
                    <div class="kpi-sub">{{ $kpi['sub'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <!-- ── Detailed Data Table ── -->
    <table class="data-table">
        @if ($type === 'parcels')
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-right">Processed</th>
                    <th class="text-right">Delivered</th>
                    <th class="text-right">Overdue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] ?? [] as $row)
                    <tr>
                        <td><strong>{{ $row['date'] instanceof \Illuminate\Support\Carbon ? $row['date']->format('M j, Y') : $row['date'] }}</strong></td>
                        <td class="text-right">{{ number_format($row['processed']) }}</td>
                        <td class="text-right">{{ number_format($row['delivered']) }}</td>
                        <td class="text-right">{{ number_format($row['overdue']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No data found in range.</td></tr>
                @endforelse
            </tbody>
        @elseif ($type === 'delivery')
            <thead>
                <tr>
                    <th>Barangay / Cluster</th>
                    <th class="text-right">Total Parcels</th>
                    <th class="text-right">Delivered</th>
                    <th class="text-right">Overdue</th>
                    <th class="text-right">Delivery Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] ?? [] as $row)
                    <tr>
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td class="text-right">{{ number_format($row['count']) }}</td>
                        <td class="text-right">{{ number_format($row['delivered']) }}</td>
                        <td class="text-right">{{ number_format($row['overdue']) }}</td>
                        <td class="text-right"><strong>{{ $row['rate'] }}%</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No data found in range.</td></tr>
                @endforelse
            </tbody>
        @elseif ($type === 'riders')
            <thead>
                <tr>
                    <th>Courier</th>
                    <th>Assigned Area</th>
                    <th class="text-right">Parcels</th>
                    <th class="text-right">Delivered</th>
                    <th class="text-right">Avg Del. Time</th>
                    <th class="text-right">Success Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] ?? [] as $row)
                    <tr>
                        <td><strong>{{ $row['rider']->name ?? '—' }}</strong></td>
                        <td>{{ $row['area'] ?? '—' }}</td>
                        <td class="text-right">{{ number_format($row['total']) }}</td>
                        <td class="text-right">{{ number_format($row['delivered']) }}</td>
                        <td class="text-right">{{ $row['avg_del_min'] ? $row['avg_del_min'].' min' : '—' }}</td>
                        <td class="text-right"><strong>{{ $row['success_pct'] }}%</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No courier data in range.</td></tr>
                @endforelse
            </tbody>
        @elseif ($type === 'branches')
            <thead>
                <tr>
                    <th>Barangay / Station</th>
                    <th class="text-right">Total Throughput</th>
                    <th class="text-right">Delivered</th>
                    <th class="text-right">Overdue</th>
                    <th class="text-right">Share of Hub Volume</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] ?? [] as $row)
                    <tr>
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td class="text-right">{{ number_format($row['count']) }}</td>
                        <td class="text-right">{{ number_format($row['delivered']) }}</td>
                        <td class="text-right">{{ number_format($row['overdue']) }}</td>
                        <td class="text-right"><strong>{{ $row['share_pct'] }}%</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No branch throughput data in range.</td></tr>
                @endforelse
            </tbody>
        @else
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Barangay</th>
                    <th>Assigned Courier</th>
                    <th>Received At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] ?? [] as $p)
                    <tr>
                        <td><strong>{{ $p->tracking_no }}</strong></td>
                        <td>{{ $p->area?->name ?? '—' }}</td>
                        <td>{{ $p->rider?->name ?? '—' }}</td>
                        <td>{{ $p->received_at?->format('M j, Y h:i A') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No parcels in this range.</td></tr>
                @endforelse
            </tbody>
        @endif
    </table>

    <div class="footer">
        Mimoo Logistics Inc. • Confidential Sorting Center Operations Report • Generated automatically from system records
    </div>

</body>
</html>
