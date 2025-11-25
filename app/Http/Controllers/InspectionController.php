<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http; // Untuk tembak API Node JS
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


class InspectionController extends Controller
{
    public function index(Request $request, $dispo)
    {
        $plant = $request->query('plant', '3000'); // Default plant 3000
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";

        $inspectionLots = [];
        $errorMsg = null;

        // 1. Ambil Kredensial dari Redis
        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);
                
                // 2. Request ke API SAP (Node.js Service)
                // Timeout 30 detik karena SAP kadang lambat
                $response = Http::timeout(30)->get('http://127.0.0.1:4003/api/get_insp_lot', [
                    'plant'    => $plant,
                    'username' => $userData['username'],
                    'password' => $userData['password'], // Password diambil dari Redis (Aman)
                    'dispo'    => $dispo
                ]);

                if ($response->successful()) {
                    $inspectionLots = $response->json()['data'] ?? [];
                } else {
                    $errorMsg = 'Gagal mengambil data dari SAP. Server merespon error.';
                }

            } catch (\Exception $e) {
                Log::error("Error fetching inspection lots: " . $e->getMessage());
                $errorMsg = 'Terjadi kesalahan koneksi ke server SAP.';
            }
        } else {
            // Jika sesi Redis hilang, middleware auth_custom akan handle, 
            // tapi untuk safety kita return kosong.
            return to_route('login')->withErrors(['username' => 'Sesi habis.']);
        }

        // 3. Render View Inertia
        return Inertia::render('Inspection/List', [
            'initialLots' => $inspectionLots,
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
                $allLots = $response->json()['data'] ?? [];
                $targetLot = collect($allLots)->firstWhere('PRUEFLOS', $lotNumber);
            }
        } catch (\Exception $e) {
        }
        if (!$targetLot) {
            return to_route('inspection.index', ['dispo' => $dispo, 'plant' => $plant])
                    ->withErrors(['error' => 'Data Inspection Lot tidak ditemukan atau akses ditolak.']);
        }
        return Inertia::render('Inspection/Form', [
            'lotNumber' => $lotNumber,
            'authUser'  => $userData,
            'plantCode' => $plant,
            'dispoCode' => $dispo,
            'lotData'   => $targetLot, // <--- INI KUNCINYA
        ]);
    }
}
