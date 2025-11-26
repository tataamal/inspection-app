<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


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

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);
                
                $response = Http::timeout(60)->get('http://127.0.0.1:4003/api/get_insp_lot', [
                    'plant'    => $plant,
                    'username' => $userData['username'],
                    'password' => $userData['password'], 
                    'dispo'    => $dispo
                ]);

                if ($response->successful()) {
                    $jsonResponse = $response->json();
                    
                    // [PERBAIKAN DISINI] 
                    // Mengambil key 'data' karena Node.js sekarang mengirim { data: [...], ... }
                    $inspectionLots = $jsonResponse['data'] ?? [];
                    
                    // Fallback: Jaga-jaga jika API masih mengirim format lama
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
            $response = Http::timeout(30)->get('http://127.0.0.1:4003/api/get_insp_lot', [
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
}
