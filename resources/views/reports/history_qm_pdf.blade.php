<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Quality Management</title>
    <style>
        @page {
            /* Top Right Bottom Left */
            /* Margin Bawah diset 1cm agar konten berhenti sebelum menabrak footer */
            margin: 0.5cm 1cm 1cm 1cm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8pt;
            color: #000000; /* UBAH KE HITAM PEKAT */
            line-height: 1.15;
        }
        
        /* --- HEADER --- */
        .header {
            width: 100%;
            padding-bottom: 5px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000000; /* Border Hitam */
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo-img { max-height: 40px; }
        
        .company-name { 
            font-size: 14pt; 
            font-weight: 900; /* Extra Bold */
            color: #000000; 
            text-transform: uppercase; 
        }
        .system-name { 
            font-size: 8pt; 
            color: #000000; 
            font-weight: bold; 
            letter-spacing: 1px; 
            margin-top: 2px;
        }
        .header-right { 
            text-align: right; 
            font-size: 7.5pt; 
            color: #000000; 
            font-weight: bold;
        }
        
        /* --- TITLE & FILTERS --- */
        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: 900;
            margin-bottom: 5px;
            color: #000000;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .filter-info {
            text-align: center;
            font-size: 7.5pt;
            color: #333333; /* Sedikit lebih gelap */
            margin-bottom: 5px;
            font-weight: bold;
            font-style: italic;
        }

        /* --- SUMMARY BOX --- */
        .summary-box {
            text-align: right;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .summary-item {
            display: inline-block;
            margin-left: 15px;
            font-weight: bold;
        }
        .summary-value {
            border: 1px solid #000;
            padding: 2px 6px;
            background-color: #f3f4f6;
            margin-left: 5px;
        }

        /* --- TABLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt; /* Font sedikit diperbesar */
        }
        table.data-table th {
            background-color: #e5e7eb; /* Abu lebih gelap */
            border: 1px solid #000000;
            padding: 6px 4px;
            text-align: left;
            text-transform: uppercase;
            font-weight: 900;
            color: #000000;
        }
        table.data-table td {
            border: 1px solid #000000; /* Border Hitam */
            padding: 5px 4px;
            vertical-align: top;
            color: #000000;
        }
        /* Zebra Striping */
        table.data-table tbody tr:nth-child(even) { background-color: #f3f4f6; }

        /* --- COLUMN STYLES --- */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Sub-text styles: Darker & Bolder */
        .text-xs { 
            font-size: 7pt; 
            color: #333333; /* Jangan abu muda, ganti ke abu tua/hitam */
            font-weight: normal;
        }
        .text-code { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 0; /* Kotak agar lebih formal saat print */
            font-weight: bold;
            font-size: 6.5pt;
            border: 1px solid #000;
        }
        /* High Contrast for Print */
        .status-success { background-color: #ffffff; color: #000000; border: 1px solid #000000; }
        .status-error { background-color: #000000; color: #ffffff; border: 1px solid #000000; }

        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            font-size: 7pt; color: #000000; border-top: 1px solid #000000;
            padding-top: 4px; text-align: center; font-weight: bold;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 20%;">
                    <img src="{{ $logo_path }}" class="logo-img" alt="Logo">
                </td>
                <td style="width: 60%; text-align: center;">
                    <div class="company-name">PT. Kayu Mebel Indonesia</div>
                    <div class="system-name">Quality Management System Dashboard</div>
                </td>
                <td class="header-right" style="width: 20%;">
                    <div>User: {{ $user['username'] ?? 'Guest' }}</div>
                    <div>Cetak: {{ $generated_at }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Laporan Riwayat Inspeksi (UD)</div>
    
    <div class="filter-info">
        Filter Applied: 
        @if($filters['start'] || $filters['end'])
            [ Periode: {{ $filters['start'] ?? '...' }} s/d {{ $filters['end'] ?? 'Now' }} ]
        @else
            [ Semua Periode ]
        @endif
        @if($filters['status']) [ Status: {{ $filters['status'] }} ] @endif
        @if($filters['section']) [ Bagian: {{ $filters['section'] }} ] @endif
        @if($filters['search']) [ Search: "{{ $filters['search'] }}" ] @endif
    </div>

    <!-- SUMMARY SECTION (BARU) -->
    <div class="summary-box">
        <div class="summary-item">
            Total Data: <span class="summary-value">{{ count($data) }}</span>
        </div>
        <div class="summary-item">
            Total Qty (SUCCESS): <span class="summary-value" style="background-color: #d1fae5; border-color: #059669;">{{ number_format($total_qty ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                
                @if(!isset($hidden_columns['date']))
                <th style="width: 9%;">Tanggal</th>
                @endif
                
                @if(!isset($hidden_columns['so']))
                <th style="width: 11%;">Sales Order</th>
                @endif

                @if(!isset($hidden_columns['buyer']))
                <th style="width: 12%;">Buyer / PO</th>
                @endif

                @if(!isset($hidden_columns['material']))
                <th style="width: 22%;">Material</th>
                @endif

                @if(!isset($hidden_columns['order']))
                <th style="width: 11%;">Order / Lot</th>
                @endif

                @if(!isset($hidden_columns['qty']))
                <th style="width: 8%;" class="text-right">Qty</th>
                @endif

                @if(!isset($hidden_columns['status']))
                <th style="width: 14%;" class="text-center">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center font-bold">{{ $index + 1 }}</td>
                
                @if(!isset($hidden_columns['date']))
                <td>
                    <div class="font-bold">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</div>
                    <div class="text-xs">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['so']))
                <td>
                    <div class="font-bold text-code">{{ $item->sales_order ?? '-' }}</div>
                    <div class="text-xs">Item: {{ $item->sales_item ?? '' }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['buyer']))
                <td>
                    <div class="font-bold">{{ Str::limit($item->buyer_name, 25) }}</div>
                    <div class="text-xs text-code">{{ $item->customer_po ?? '-' }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['material']))
                <td>
                    <div class="font-bold">{{ $item->material_desc }}</div>
                    <!-- CLEAN DATA: Hapus Leading Zero pada Material Code -->
                    <div class="text-xs text-code">{{ ltrim($item->material_code, '0') }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['order']))
                <td>
                    <!-- CLEAN DATA: Hapus Leading Zero pada Batch jika perlu, atau biarkan -->
                    @if($item->batch)
                    <div><span class="text-xs">Batch:</span> <span class="text-code font-bold">{{ $item->batch }}</span></div>
                    @endif
                    
                    <div style="margin-top: 2px;">
                        <span class="text-xs">Lot:</span> <span class="text-code font-bold">{{ $item->prueflos }}</span>
                    </div>

                    @if($item->order_number)
                    <div><span class="text-xs">Ord:</span> <span class="text-code">{{ $item->order_number }}</span></div>
                    @endif
                </td>
                @endif

                @if(!isset($hidden_columns['qty']))
                <td class="text-center font-bold">
                    <span class="font-bold" style="font-size: 9pt;">{{ number_format($item->quantity, 0, ',', '.') }}</span>
                    <!-- CLEAN DATA: ST -> PC -->
                    <span class="text-xs font-bold">{{ $item->uom == 'ST' ? 'PC' : $item->uom }}</span>
                </td>
                @endif

                @if(!isset($hidden_columns['status']))
                <td class="text-center">
                    <div style="margin-bottom: 2px;">
                        @if($item->status == 'SUCCESS')
                            <span class="status-badge status-success">SUCCESS / UD</span>
                        @else
                            <span class="status-badge status-error">GAGAL UD</span>
                        @endif
                    </div>
                    @if($item->status == 'ERROR')
                        <div style="font-size: 6pt; color: #000000; font-style: italic; margin-top: 2px; border-top: 1px dashed #000;">
                            {{ Str::limit($item->sap_message, 50) }}
                        </div>
                    @endif
                    <div class="text-xs" style="margin-top:4px;"><strong>NIK:</strong> {{ $item->inspector_nik }}</div>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; font-style: italic; font-weight: bold;">
                    TIDAK ADA DATA YANG DITEMUKAN SESUAI FILTER.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by KMI Inspection System | Halaman <span class="page-number"></span>
    </div>

</body>
</html>