<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Quality Management</title>
    <style>
        @page {
            margin: 0.8cm 1.2cm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8.5pt;
            color: #1a1a1a;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        
        /* --- COMPACT HEADER SECTION --- */
        .header {
            width: 100%;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 2px solid #10b981;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        
        .header-left {
            width: 25%;
            text-align: left;
        }
        
        .logo-img {
            max-width: 60px;
            max-height: 40px;
        }
        
        .header-center {
            width: 50%;
            text-align: center;
        }
        
        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        .system-name {
            font-size: 8pt;
            color: #10b981;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 2px 0 0 0;
        }
        
        .header-right {
            width: 25%;
            text-align: right;
            font-size: 7.5pt;
            color: #4b5563;
            line-height: 1.4;
        }
        
        .header-right strong {
            color: #1f2937;
        }

        /* --- COMPACT REPORT TITLE --- */
        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 8px 0 10px 0;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- COMPACT TABLE STYLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-top: 5px;
        }
        
        table.data-table th {
            background-color: #f3f4f6;
            border: 1px solid #9ca3af;
            padding: 5px 4px;
            text-align: left;
            text-transform: uppercase;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.3px;
            color: #000000;
        }
        
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 4px 5px;
            vertical-align: middle;
        }
        
        /* Zebra Striping */
        table.data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* --- COMPACT CELL STYLING --- */
        .cell-number {
            text-align: center;
            font-weight: bold;
            color: #6b7280;
            font-size: 7pt;
        }
        
        .cell-date {
            font-weight: 600;
            color: #1f2937;
            font-size: 7.5pt;
            line-height: 1.3;
        }
        
        .inspection-lot {
            font-weight: bold;
            color: #064e3b;
            font-size: 8pt;
        }
        
        .plant-info {
            color: #6b7280;
            font-size: 6.5pt;
            margin-top: 1px;
        }
        
        .material-code {
            font-weight: bold;
            color: #1f2937;
            font-size: 7.5pt;
        }
        
        .material-desc {
            color: #6b7280;
            font-size: 6.5pt;
            margin-top: 1px;
        }
        
        .batch-info {
            line-height: 1.4;
            font-size: 7.5pt;
        }
        
        .batch-label {
            font-weight: bold;
            color: #374151;
        }
        
        .batch-value {
            color: #1f2937;
        }
        
        .qty-value {
            text-align: right;
            font-weight: bold;
            font-size: 8pt;
            color: #064e3b;
        }
        
        .uom-label {
            color: #6b7280;
            font-size: 6.5pt;
            font-weight: normal;
        }
        
        .nik-box {
            background-color: #f3f4f6;
            padding: 3px 6px;
            border-radius: 3px;
            text-align: center;
            font-weight: bold;
            color: #1f2937;
            font-size: 7.5pt;
            display: inline-block;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 6.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .status-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .status-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* --- COMPACT FOOTER --- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
            font-size: 7pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            text-align: center;
            background-color: white;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-style: italic;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <!-- COMPACT HEADER (ONE LINE) -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img src="{{ $logo_path ?? '' }}" class="logo-img" alt="Logo">
                </td>
                <td class="header-center">
                    <div class="company-name">PT. Kayu Mebel Indonesia</div>
                    <div class="system-name">Quality Management System</div>
                </td>
                <td class="header-right">
                    <div><strong>Dicetak:</strong> {{ $user['username'] }} ({{ $user['nik'] }})</div>
                    <div><strong>Tanggal:</strong> {{ $generated_at }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- COMPACT REPORT TITLE -->
    <div class="report-title">Laporan Riwayat Usage Decision (UD)</div>

    <!-- COMPACT DATA TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">No</th>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 13%;">Inspection Lot</th>
                <th style="width: 28%;">Material</th>
                <th style="width: 17%;">Batch / Order</th>
                <th style="width: 10%; text-align: right;">Qty</th>
                <th style="width: 9%; text-align: center;">NIK</th>
                <th style="width: 9%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="cell-number">{{ $index + 1 }}</td>
                
                <td class="cell-date">
                    {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}<br>
                    <small style="color: #6b7280; font-size: 6.5pt;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</small>
                </td>
                
                <td>
                    <div class="inspection-lot">{{ $item->prueflos }}</div>
                    <div class="plant-info">Plant: {{ $item->plant }}</div>
                </td>
                
                <td>
                    <div class="material-code">{{ $item->material_code }}</div>
                    <div class="material-desc">{{ $item->material_desc ?? '-' }}</div>
                </td>
                
                <td class="batch-info">
                    <div><span class="batch-label">B:</span> {{ $item->batch ?? '-' }}</div>
                    <div><span class="batch-label">O:</span> {{ $item->order_number ?? '-' }}</div>
                </td>
                
                <td class="qty-value">
                    {{ number_format($item->quantity, 0, ',', '.') }}
                    <span class="uom-label">{{ $item->uom == 'ST' ? 'PC' : $item->uom }}</span>
                </td>
                
                <td style="text-align: center;">
                    <div class="nik-box">{{ $item->inspector_nik }}</div>
                </td>
                
                <td style="text-align: center;">
                    @if($item->status == 'SUCCESS')
                        <span class="status-badge status-success">OK</span>
                    @else
                        <span class="status-badge status-error">{{ $item->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty-state">
                    Tidak ada data riwayat yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- COMPACT FOOTER -->
    <div class="footer">
        PT. Kayu Mebel Indonesia &bull; Quality Management System &bull; Hal. <span class="page-number"></span>
    </div>

</body>
</html>