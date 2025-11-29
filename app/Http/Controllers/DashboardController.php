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
            } catch (\Exception $e) {
                // Handle error decrypt
            }
        }
        $myMrpList = MappingUserPlant::where('nik', $userData['nik'])
                        ->select('id', 'plant', 'mrp as code', 'nama_karyawan as name') 
                        ->get();
        $historyList = [];
        if ($userData['username'] === 'KMI-U124' && $userData['nik'] === '10001069') {
            $validNiks = DB::table('mapping_user_plant')
                            ->where('sap_id', $userData['username'])
                            ->pluck('nik')
                            ->toArray();
            if (empty($validNiks)) {
                $validNiks = [$userData['nik']];
            }

            $historyList = DB::table('history_quality_management')
                            ->whereIn('inspector_nik', $validNiks)
                            ->orderBy('created_at', 'desc')
                            ->limit(100)
                            ->get();

        } else {
            $historyList = DB::table('history_quality_management')
                            ->where('inspector_nik', $userData['nik'])
                            ->orderBy('created_at', 'desc')
                            ->limit(50) // Batasi 50 terakhir
                            ->get();
        }

        return Inertia::render('Dashboard', [
            'authUser' => [
                'username' => $userData['username'],
                'nik'      => $userData['nik']
            ],
            'mrpList' => $myMrpList,
            'historyList' => $historyList
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
            } catch (\Exception $e) { /* Handle error */ }
        }
        if ($userData['username'] !== 'KMI-U124' || $userData['nik'] !== '10001069') {
            abort(403, 'Unauthorized action.');
        }
        $validNiks = DB::table('mapping_user_plant')
                        ->where('sap_id', $userData['username'])
                        ->pluck('nik')
                        ->toArray();

        if (empty($validNiks)) {
            $validNiks = [$userData['nik']];
        }
        $query = DB::table('history_quality_management')
                    ->whereIn('inspector_nik', $validNiks);
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $historyData = $query->orderBy('created_at', 'desc')->get();
        $hiddenColumns = [];
        $columnsToCheck = ['date', 'lot', 'material', 'so', 'buyer', 'order', 'qty', 'ud', 'status'];
        
        foreach ($columnsToCheck as $col) {
            if ($request->has("hide_{$col}")) {
                $hiddenColumns[$col] = true;
            }
        }

        // 6. Generate PDF
        $pdf = Pdf::loadView('reports.history_qm_pdf', [
            'data' => $historyData,
            'user' => $userData,
            'generated_at' => Carbon::now()->format('d F Y, H:i:s'),
            'logo_path' => public_path('images/KMI.png'),
            'filters' => [
                'start' => $request->start_date,
                'end' => $request->end_date
            ],
            'hidden_columns' => $hiddenColumns 
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_History_QM_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}