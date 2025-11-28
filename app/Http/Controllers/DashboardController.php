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

        // 1. Ambil MRP List (Logic Lama)
        $myMrpList = MappingUserPlant::where('nik', $userData['nik'])
                        ->select('id', 'plant', 'mrp as code', 'nama_karyawan as name') 
                        ->get();

        // 2. [TAMBAHAN] Ambil History List jika user adalah target khusus
        $historyList = [];
        
        // Cek apakah ini user khusus (bisa via config, env, atau hardcode sesuai request)
        if ($userData['username'] === 'KMI-U124' && $userData['nik'] === '10001069') {
            $historyList = DB::table('history_quality_management')
                            ->where('inspector_nik', $userData['nik'])
                            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
                            ->limit(100) // Batasi agar tidak terlalu berat
                            ->get();
        }

        return Inertia::render('Dashboard', [
            'authUser' => [
                'username' => $userData['username'],
                'nik'      => $userData['nik']
            ],
            'mrpList' => $myMrpList,
            'historyList' => $historyList // [PENTING] Kirim data ke Vue props
        ]);
    }

    public function exportHistoryPdf(Request $request)
    {
        // 1. Ambil Session User
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";
        
        $userData = ['username' => 'Guest', 'nik' => '0000']; 

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);
            } catch (\Exception $e) { /* Handle error */ }
        }

        // 2. Security Check
        if ($userData['username'] !== 'KMI-U124' || $userData['nik'] !== '10001069') {
            abort(403, 'Unauthorized action.');
        }

        // 3. [PERBAIKAN LOGIKA] Ambil Data History
        // Cek tabel mapping untuk mendapatkan NIK yang valid berdasarkan SAP ID yang login
        $validNiks = DB::table('mapping_user_plant')
                        ->where('sap_id', $userData['username']) // Filter berdasarkan SAP ID (misal: KMI-U124)
                        ->pluck('nik') // Ambil semua NIK yang terhubung (jika ada lebih dari 1)
                        ->toArray();

        // Jika tidak ada mapping ditemukan, fallback ke NIK dari session (opsional, atau kosongkan)
        if (empty($validNiks)) {
            $validNiks = [$userData['nik']];
        }

        // Query history menggunakan NIK yang valid dari mapping
        $historyData = DB::table('history_quality_management')
                        ->whereIn('inspector_nik', $validNiks) // Gunakan whereIn untuk safety
                        ->orderBy('created_at', 'desc')
                        ->limit(200)
                        ->get();

        // 4. Generate PDF
        $pdf = Pdf::loadView('reports.history_qm_pdf', [
            'data' => $historyData,
            'user' => $userData,
            'generated_at' => Carbon::now()->format('d F Y, H:i:s'),
            'logo_path' => public_path('images/KMI.png')
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_History_QM_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}
