<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Quality Management</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8pt;
            color: #1a1a1a;
            line-height: 1.2;
        }
        
        /* --- HEADER --- */
        .header {
            width: 100%;
            padding-bottom: 5px;
            margin-bottom: 10px;
            border-bottom: 2px solid #10b981;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo-img { max-height: 35px; }
        .company-name { font-size: 12pt; font-weight: bold; color: #064e3b; text-transform: uppercase; }
        .system-name { font-size: 7pt; color: #10b981; font-weight: bold; letter-spacing: 1px; }
        .header-right { text-align: right; font-size: 7pt; color: #4b5563; }
        
        /* --- TITLE & FILTERS --- */
        .report-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #064e3b;
            text-transform: uppercase;
        }
        .filter-info {
            text-align: center;
            font-size: 7pt;
            color: #6b7280;
            margin-bottom: 10px;
            font-style: italic;
        }

        /* --- TABLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }
        table.data-table th {
            background-color: #f3f4f6;
            border: 1px solid #9ca3af;
            padding: 4px;
            text-align: left;
            text-transform: uppercase;
            font-weight: bold;
            color: #1f2937;
        }
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
        }
        /* Zebra Striping */
        table.data-table tbody tr:nth-child(even) { background-color: #f9fafb; }

        /* --- COLUMN STYLES --- */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-xs { font-size: 6.5pt; color: #6b7280; }
        .text-code { font-family: 'Courier New', Courier, monospace; }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 6pt;
        }
        .status-success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            font-size: 6pt; color: #9ca3af; border-top: 1px solid #e5e7eb;
            padding-top: 2px; text-align: center;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 20%;">
                    @if(isset($logo_path) && file_exists($logo_path))
                        <img src="{{ $logo_path }}" class="logo-img" alt="Logo">
                    @else
                        <strong>KMI</strong>
                    @endif
                </td>
                <td style="width: 60%; text-align: center;">
                    <div class="company-name">PT. Kayu Mebel Indonesia</div>
                    <div class="system-name">Quality Management System</div>
                </td>
                <td class="header-right" style="width: 20%;">
                    <div><strong>User:</strong> {{ $user['username'] ?? 'Guest' }}</div>
                    <div><strong>Tgl:</strong> {{ $generated_at }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Laporan Riwayat Inspeksi</div>
    
    <!-- Tampilkan Info Filter yang Dipakai -->
    <div class="filter-info">
        Filter: 
        @if($filters['start'] || $filters['end'])
            Periode {{ $filters['start'] ?? '...' }} s/d {{ $filters['end'] ?? 'Now' }}
        @else
            Semua Periode
        @endif
        @if($filters['status']) | Status: {{ $filters['status'] }} @endif
        @if($filters['section']) | Bagian: {{ $filters['section'] }} @endif
        @if($filters['search']) | Pencarian: "{{ $filters['search'] }}" @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">No</th>
                
                @if(!isset($hidden_columns['date']))
                <th style="width: 8%;">Tanggal</th>
                @endif
                

                @if(!isset($hidden_columns['so']))
                <th style="width: 10%;">Sales Order</th>
                @endif

                @if(!isset($hidden_columns['buyer']))
                <th style="width: 12%;">Buyer / PO</th>
                @endif

                @if(!isset($hidden_columns['material']))
                <th style="width: 20%;">Material</th>
                @endif

                @if(!isset($hidden_columns['order']))
                <th style="width: 10%;">Batch / Order</th>
                @endif

                @if(!isset($hidden_columns['qty']))
                <th style="width: 8%;" class="text-right">Qty</th>
                @endif

                @if(!isset($hidden_columns['status']))
                <th style="width: 14%;">Status & Log</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                
                @if(!isset($hidden_columns['date']))
                <td>
                    <div class="font-bold">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</div>
                    <div class="text-xs">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['so']))
                <td>
                    <div class="font-bold text-code" style="color: #4338ca;">{{ $item->sales_order ?? '-' }}</div>
                    <div class="text-xs">Item: {{ $item->sales_item ?? '' }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['buyer']))
                <td>
                    <div class="font-bold" style="font-size: 6.5pt;">{{ Str::limit($item->buyer_name, 25) }}</div>
                    <div class="text-xs text-code">{{ $item->customer_po ?? '-' }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['material']))
                <td>
                    <div style="font-size: 7pt;">{{ $item->material_desc }}</div>
                    <div class="text-xs text-code">{{ $item->material_code }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['order']))
                <td>
                    @if($item->batch)
                    <div><span class="text-xs">B:</span> <span class="text-code font-bold">{{ $item->batch }}</span></div>
                    @endif
                    @if($item->order_number)
                    <div><span class="text-xs">O:</span> <span class="text-code">{{ $item->order_number }}</span></div>
                    @endif
                </td>
                @endif

                @if(!isset($hidden_columns['qty']))
                <td class="text-left">
                    <div class="font-bold">{{ number_format($item->quantity, 0, ',', '.') }}</div>
                    <div class="text-xs">{{ $item->uom }}</div>
                </td>
                @endif

                @if(!isset($hidden_columns['status']))
                <td>
                    <div style="margin-bottom: 2px;">
                        @if($item->status == 'SUCCESS')
                            <span class="status-badge status-success">SUCCESS</span>
                        @else
                            <span class="status-badge status-error">GAGAL UD</span>
                        @endif
                    </div>
                    @if($item->status == 'ERROR')
                        <div style="font-size: 6pt; color: #991b1b; font-style: italic;">
                            {{ Str::limit($item->sap_message, 40) }}
                        </div>
                    @endif
                    <div class="text-xs" style="margin-top:2px;">By: {{ $item->inspector_nik }}</div>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #9ca3af; font-style: italic;">
                    Tidak ada data yang ditemukan sesuai filter.
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