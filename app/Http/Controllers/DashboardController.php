<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\MappingUserPlant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DashboardController extends Controller
{

    private function determineSection($item)
    {
        $snapshot = isset($item->full_lot_snapshot) ? json_decode($item->full_lot_snapshot, true) : [];
        
        $dispo = $snapshot['DISPO'] ?? '';
        
        $maktx = $item->material_desc ?? ($snapshot['KTEXTMAT'] ?? ''); 
        if (in_array($dispo, ['D24', 'G32'])) {
            return 'Packing';
        }

        if (in_array($dispo, ['G31', 'D23', 'D28', 'MA4', 'MA7', 'MF4'])) {
            return 'Painting';
        }
        if ($dispo === 'D22' && stripos($maktx, 'UNF') !== false) {
            return 'Painting';
        }

        return 'Other';
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('section')) {
            $section = $request->section;
            if ($section === 'Packing') {
                $query->whereIn('full_lot_snapshot->DISPO', ['D24', 'G32']);
            } elseif ($section === 'Painting') {
                $query->where(function($q) {
                    $q->whereIn('full_lot_snapshot->DISPO', ['G31', 'D23', 'D28', 'MA4', 'MA7', 'MF4'])
                      ->orWhere(function($subQ) {
                          $subQ->where('full_lot_snapshot->DISPO', 'D22')
                               ->where('material_desc', 'LIKE', '%UNF%');
                      });
                });
            }
        }

        // 4. Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            // Exact Match ("...")
            if (preg_match('/"([^"]+)"/', $search, $matches)) {
                $term = $matches[1];
                $query->where(function($q) use ($term) {
                    $q->where('prueflos', $term)
                      ->orWhere('batch', $term)
                      ->orWhere('material_code', $term)
                      ->orWhere('order_number', $term);
                });
            } else {
                // Multi-keyword Search
                $terms = preg_split('/[\s,]+/', strtolower($search), -1, PREG_SPLIT_NO_EMPTY);
                $query->where(function($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where(function($subQ) use ($term) {
                            $subQ->where(DB::raw('LOWER(prueflos)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(material_desc)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(material_code)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(sales_order)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(buyer_name)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(customer_po)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(order_number)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(batch)'), 'LIKE', "%{$term}%")
                                 ->orWhere(DB::raw('LOWER(ud_code)'), 'LIKE', "%{$term}%");
                        });
                    }
                });
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";
        $userData = [
            'username' => 'Guest', 
            'nik' => '0000',
            'sap_status' => 'offline'
        ];

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);
            } catch (\Exception $e) {}
        }

        $myMrpList = MappingUserPlant::where('nik', $userData['nik'])
                        ->select('id', 'plant', 'mrp as code', 'nama_karyawan as name') 
                        ->get();

        // --- QUERY BUILDER ---
        $query = DB::table('history_quality_management');

        // A. Filter Role (Admin vs User)
        if (!isset($userData['role']) || $userData['role'] !== 'admin') {
            $query->where('inspector_nik', $userData['nik']);
        }

        // B. Terapkan Filter (Search, Date, Status) di Server Side
        $this->applyFilters($query, $request);

        // ==========================================================
        // POINT UTAMA: Hitung Total Qty (Hanya Status SUCCESS)
        // Kita clone query agar filternya terbawa, tapi tidak merusak pagination
        // ==========================================================
        $qtyQuery = clone $query;
        $totalQty = $qtyQuery->where('status', 'SUCCESS')->sum('quantity');

        // C. Eksekusi Pagination (6 per halaman)
        $historyList = $query->orderBy('created_at', 'desc')
                             ->paginate(6)
                             ->withQueryString(); 

        // D. Transformasi Data
        $historyList->through(function ($item) {
            $item->section = $this->determineSection($item);
            return $item;
        });

        return Inertia::render('Dashboard', [
            'authUser' => [
                'username' => $userData['username'],
                'nik'      => $userData['nik'],
                'role'     => $userData['role'] ?? 'user'
            ],
            'mrpList' => $myMrpList,
            'historyList' => $historyList,
            'totalQty' => (float) $totalQty, // Kirim ke Vue sebagai Number
            'filters' => $request->all(['search', 'status', 'section', 'startDate', 'endDate'])
        ]);
    }

    public function exportHistoryPdf(Request $request)
    {
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";
        $userData = ['username' => 'Guest', 'nik' => '0000']; 

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);
            } catch (\Exception $e) { }
        }

        if (!isset($userData['role']) || $userData['role'] !== 'admin') {
             abort(403, 'Unauthorized action.');
        }

        $query = DB::table('history_quality_management');

        // Terapkan Filter yang sama persis
        $this->applyFilters($query, $request);

        // Ambil data
        $historyData = $query->orderBy('created_at', 'desc')->get();
        
        // Transform Data
        $historyData->transform(function($item) {
            $item->section = $this->determineSection($item);
            return $item;
        });

        // ==========================================================
        // POINT UTAMA: Hitung Total Qty untuk PDF
        // Hitung dari collection yang sudah ditarik (agar hemat query DB)
        // ==========================================================
        $totalQty = $historyData->where('status', 'SUCCESS')->sum('quantity');

        // Kolom Sembunyi
        $hiddenColumns = [];
        $columnsToCheck = ['date', 'lot', 'material', 'so', 'buyer', 'order', 'qty', 'ud', 'status'];
        foreach ($columnsToCheck as $col) {
            if ($request->has("hide_{$col}")) {
                $hiddenColumns[$col] = true;
            }
        }

        $pdf = Pdf::loadView('reports.history_qm_pdf', [
            'data' => $historyData,
            'user' => $userData,
            'total_qty' => $totalQty, // Kirim ke View PDF
            'generated_at' => Carbon::now()->format('d F Y, H:i:s'),
            'logo_path' => public_path('images/KMI.png'),
            'filters' => [
                'start' => $request->startDate ?? $request->start_date,
                'end' => $request->endDate ?? $request->end_date,
                'status' => $request->status,
                'section' => $request->section,
                'search' => $request->search
            ],
            'hidden_columns' => $hiddenColumns 
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_History_QM_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}