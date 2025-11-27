<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\HistoryQualityManagement;


class InspectionController extends Controller
{
    public function index(Request $request, $dispo)
    {   
        // Pastikan plant diambil dari query string, default ke 3000 jika kosong
        $plant = $request->query('plant', '3000'); 
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";

        $inspectionLots = [];
        $components = []; 
        $errorMsg = null;
        $sapBaseUrl = config('services.sap.url');

        if (!app()->runningInConsole()) {

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);

                $response = Http::timeout(60)->get("{$sapBaseUrl}/api/get_insp_lot", [
                    'plant'    => $plant,
                    'username' => $userData['username'],
                    'password' => $userData['password'], 
                    'dispo'    => $dispo
                ]);

                if ($response->successful()) {
                    $jsonResponse = $response->json();

                    // [PERBAIKAN DISINI] 
                    // mengambil key 'data' karena Node.js sekarang mengirim { data: [...], ... }
                    $inspectionLots = $jsonResponse['data'] ?? [];

                    // fallback: format lama
                    if (empty($inspectionLots)) {
                        $inspectionLots = $jsonResponse['data_insp_lot'] ?? [];
                    }

                    $components = $jsonResponse['data_components'] ?? [];

                } else {
                    $errorMsg = 'Gagal mengambil data dari SAP. Status: ' . $response->status();
                    Log::error("SAP Error Response: " . $response->body());
                }

            } catch (\Exception $e) {
                Log::error("Error fetching inspection lots: " . $e->getMessage());
                $errorMsg = 'Terjadi kesalahan koneksi ke server SAP.';
            }

        } else {
            return to_route('login')->withErrors(['username' => 'Sesi habis.']);
        }

    } else {

        // Jika dijalankan oleh CLI (artisan, wayfinder, dll)
        // jangan memanggil Redis, Crypt, atau API
        $inspectionLots = [];
        $components = [];
    }

        return Inertia::render('Inspection/List', [
            'initialLots' => $inspectionLots,
            'components'  => $components,
            'dispoCode'   => $dispo,
            'plantCode'   => $plant,
            'errorMessage'=> $errorMsg,
            'authUser'    => [
            'username' => $userData['username'] ?? 'User',
            ]
        ]);
    }

    public function showForm(Request $request, $lotNumber)
    {
        // 1. Setup Session (Sama)
        $sessionId = $request->session()->getId();
        $userData = ['username' => 'Guest', 'nik' => ''];
        $password = '';
        $sapBaseUrl = config('services.sap.url'); 

        if (Redis::exists("sap_session:{$sessionId}")) {
            try {
                $decrypted = Crypt::decryptString(Redis::get("sap_session:{$sessionId}"));
                $sessionData = json_decode($decrypted, true);
                $userData['username'] = $sessionData['username'] ?? 'User';
                $userData['nik'] = $sessionData['nik'] ?? '';
                $password = $sessionData['password'] ?? '';
            } catch (\Exception $e) {}
        }

        // 2. Ambil Parameter
        $plant = $request->query('plant', '3000');
        $dispo = $request->query('dispo', ''); 

        $targetLot = null;

        try {
            $response = Http::timeout(30)->get("{$sapBaseUrl}/api/get_insp_lot", [
                'plant'    => $plant,
                'username' => $userData['username'],
                'password' => $password,
                'dispo'    => $dispo
            ]);

            if ($response->successful()) {
                // Perhatikan: Node.js Anda sekarang mengembalikan 'data', bukan 'data_insp_lot'
                // Pastikan Node.js sudah direstart setelah perubahan terakhir.
                $allLots = $response->json()['data'] ?? []; 
                
                // --- PERBAIKAN PENCARIAN (FIX LEADING ZERO) ---
                // Kita bandingkan sebagai Integer agar "0400..." sama dengan "400..."
                $targetLot = collect($allLots)->first(function ($lot) use ($lotNumber) {
                    return (int)$lot['PRUEFLOS'] === (int)$lotNumber;
                });
            }
        } catch (\Exception $e) {
            // Silent error
        }

        // --- PERBAIKAN FALLBACK (JANGAN REDIRECT) ---
        // Jika data tidak ketemu (misal karena beda format atau API gagal),
        // Kita buat data manual agar Form tetap terbuka.
        if (!$targetLot) {
            $targetLot = [
                'PRUEFLOS'   => $lotNumber, // Gunakan nomor dari URL
                'MATNR'      => '-', 
                'KTEXTMAT'   => 'Data lot tidak ditemukan di list API (Cek Log)',
                'CHARG'      => '-',
                'AUFNR'      => null, 
                'WERKS'      => $plant,
                'LOSMENGE'   => 0,
                'MENGENEINH' => 'PC',
                'STATS'      => 'REL',
                'ENSTEHDAT'  => now()->format('Ymd')
            ];
        }

        // Return Inertia (Apapun kondisinya, halaman harus terbuka)
        return Inertia::render('Inspection/Form', [
            'lotNumber' => $lotNumber,
            'authUser'  => $userData,
            'plantCode' => $plant,
            'dispoCode' => $dispo,
            'lotData'   => $targetLot, 
        ]);
    }

    public function getComponents($aufnr)
    {
        try {
            $components = DB::table('production_t_data4')
                ->where('AUFNR', $aufnr)
                ->orderBy('RSPOS', 'asc')
                ->get();
            $components->transform(function ($item) {
                $inspectorInfo = null;

                if (!empty($item->USRISP) && !empty($item->DISPO)) {
                    $userMapping = DB::table('mapping_user_plant')
                        ->where('sap_id', $item->USRISP)
                        ->where('mrp', $item->DISPO) 
                        ->first();

                    if ($userMapping) {
                        $inspectorInfo = [
                            'nik'  => $userMapping->nik,
                            'nama' => $userMapping->nama_karyawan,
                            'sap'  => $userMapping->sap_id
                        ];
                    }
                }

                $item->inspector_details = $inspectorInfo;

                return $item;
            });

            $firstValidInspector = $components->first(function ($item) {
                return !empty($item->inspector_details);
            });

            $historyQm = $firstValidInspector ? $firstValidInspector->inspector_details : null;

            return response()->json([
                'status'  => 'success',
                'data'    => $components,
                'history' => $historyQm
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting components for AUFNR $aufnr: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkPass(Request $request) 
    {
        $lotsData = $request->input('lots', []); 
        $requestPlant = $request->input('plant'); 
        if (empty($requestPlant)) {
            return response()->json(['status' => 'error', 'message' => 'Plant wajib dikirim.'], 400);
        }
        $udConfig = [];
        if (in_array($requestPlant, ['3000', '1000', '1001'])) {
            $udConfig = ['plant' => '1000', 'ud_code_group' => 'ZI', 'ud_selected_set' => 'Z1', 'ud_code' => 'A'];
        } else if ($requestPlant == '2000') {
            $udConfig = ['plant' => '2000', 'ud_code_group' => 'ZI', 'ud_selected_set' => 'ZI', 'ud_code' => 'A'];
        } else {
            return response()->json(['status' => 'error', 'message' => "Plant {$requestPlant} tidak valid."], 400);
        }
        $sapUsername = '';
        $sapPassword = '';
        $sapNik      = '';

        try {
            $sessionId = request()->session()->getId();
            $redisKey = "sap_session:{$sessionId}";

            if (Redis::exists($redisKey)) {
                $decrypted = Crypt::decryptString(
                    Redis::get($redisKey)
                );
                $sess = json_decode($decrypted, true);
                $sapUsername = $sess['username'] ?? '';
                $sapPassword = $sess['password'] ?? '';
                $sapNik      = $sess['nik'] ?? ''; 
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Sesi Redis Error.'], 401);
        }

        if (empty($sapUsername) || empty($sapPassword) || empty($sapNik)) {
            return response()->json(['status' => 'error', 'message' => 'Sesi Invalid (NIK/Pass kosong). Login ulang.'], 401);
        }
        return response()->stream(function () use ($lotsData, $requestPlant, $udConfig, $sapUsername, $sapPassword, $sapNik) {
            $sapBaseUrl = config('services.sap.url');
            foreach ($lotsData as $lotRaw) {
                $lot = (object) $lotRaw; 
                $lotNumber = $lot->PRUEFLOS ?? null;
                if (!$lotNumber) continue; 
                $status = 'ERROR';
                $message = 'Unknown Error';

                try {
                    $payload = [
                        'prueflos'        => $lotNumber,
                        'username'        => $sapUsername,
                        'password'        => $sapPassword,
                        'nik'             => $sapNik,
                        'plant'           => $udConfig['plant'], 
                        'ud_selected_set' => $udConfig['ud_selected_set'],
                        'ud_code_group'   => $udConfig['ud_code_group'],
                        'ud_code'         => $udConfig['ud_code'],
                        'stock_posting'   => "X"
                    ];

                    $response = Http::timeout(60)->post("{$sapBaseUrl}/api/send_usage_decision", $payload);
                    $resData = $response->json();

                    if ($response->successful() && ($resData['status'] ?? '') == 'success') {
                        $status = 'SUCCESS';
                        $message = $resData['message'] ?? 'Posted';
                    } else {
                        $status = 'ERROR';
                        $message = $resData['message'] ?? 'SAP Error';
                    }

                } catch (\Exception $e) {
                    $status = 'ERROR';
                    $message = $e->getMessage();
                }

                try {
                    HistoryQualityManagement::create([
                        'prueflos'           => $lotNumber,
                        'plant'              => $requestPlant,
                        'order_number'       => $lot->AUFNR ?? null,
                        'material_code'      => $lot->MATNR ?? null,
                        'material_desc'      => $lot->KTEXTMAT ?? null,
                        'batch'              => $lot->CHARG ?? null,
                        'quantity'           => $lot->LOSMENGE ?? 0,
                        'uom'                => $lot->MENGENEINH ?? null,
                        'inspector_sap_id'   => $sapUsername, 
                        'inspector_nik'      => $sapNik,                  
                        'ud_code'            => $udConfig['ud_code'],
                        'ud_selected_set'    => $udConfig['ud_selected_set'],
                        'status'             => $status,
                        'sap_message'        => $message,
                        'full_lot_snapshot'  => $lotRaw 
                    ]);

                } catch (\Exception $dbEx) {
                    Log::error("DB Save Fail Lot {$lotNumber}: " . $dbEx->getMessage());
                }
                echo json_encode([
                    'lot' => $lotNumber,
                    'status' => $status,
                    'message' => $message
                ]) . "\n";

                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            // Tanda Selesai
            echo json_encode(['status' => 'DONE']) . "\n";
            if (ob_get_level() > 0) ob_flush();
            flush();

        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
