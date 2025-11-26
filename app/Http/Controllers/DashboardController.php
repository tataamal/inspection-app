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
                        ->select('id', 'plant', 'mrp as code', 'nama_karyawan as name') // Alias biar cocok sama UI
                        ->get();
        return Inertia::render('Dashboard', [
            'authUser' => [
                'username' => $userData['username'],
                'nik'      => $userData['nik']
            ],
            'mrpList' => $myMrpList
        ]);
    }
}
