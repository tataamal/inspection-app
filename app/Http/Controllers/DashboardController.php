<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MappingUserPlant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Session ID & Data User dari Redis
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";
        
        // Default user data (jika redis kosong/expired, middleware akan handle, 
        // tapi kita set default array biar tidak error saat dev)
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

        // 2. Ambil Data MRP (Area Kerja) user ini dari Database
        // Menggunakan model MappingUserPlant yang sudah kita buat
        $myMrpList = MappingUserPlant::where('nik', $userData['nik'])
                        ->select('id', 'plant', 'mrp as code', 'nama_karyawan as name') // Alias biar cocok sama UI
                        ->get();

        // 3. Kirim ke View (Vue) via Inertia
        return Inertia::render('Dashboard', [
            'authUser' => [
                'username' => $userData['username'],
                'nik'      => $userData['nik']
            ],
            'mrpList' => $myMrpList
        ]);
    }
}
