<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function showLogin()
    {
        return inertia('Auth/Login');
    }

    public function login(Request $request)
    {
        $sapBaseUrl = config('services.sap.url');
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
            'nik'      => ['required'],
        ]);

        // 1. Buat semua character yang masuk agar uppercase
        $username = strtoupper($credentials['username']);
        $nik = strtoupper($credentials['nik']);
        $password = $credentials['password']; // Password tidak diubah ke uppercase

        Log::info("[AUTH] Percobaan login user: {$username}");

        // 2. untuk NIK cari di database mapping_user_plant harus terdaftar di database, jika tidak ada jangan diizinkan masuk, NIK DAN SAP_ID HARUS SESUAI
        $userMapping = DB::table('mapping_user_plant')->where('nik', $nik)->where('sap_id', $username)->first();

        if (!$userMapping) {
            Log::warning("[AUTH] Gagal Login: NIK '{$nik}' atau SAP ID '{$username}' tidak terdaftar atau tidak sesuai.");
            return back()->withErrors([
                'username' => 'Login Gagal: NIK atau SAP ID tidak terdaftar/sesuai.',
            ]);
        }

        try {
            $response = Http::timeout(30)->post("{$sapBaseUrl}/api/sap-login", [
                'username' => $username,
                'password' => $password,
            ]);

            if ($response->successful()) {
                Log::info("[AUTH] Login SAP Berhasil untuk user: {$username}");
                $request->session()->regenerate();
                $sessionId = $request->session()->getId();
                $redisKey = "sap_session:{$sessionId}";
                $sapData = [
                    'username' => $username,
                    'password' => $password,
                    'nik'      => $nik,
                    'sap_status' => 'connected',
                    'role' => $userMapping->role
                ];
                Redis::setex($redisKey, 7200, Crypt::encryptString(json_encode($sapData)));
                $request->session()->put('user_nik', $nik);
                $request->session()->put('user_sap_id', $username);
                $request->session()->put('user_role', $userMapping->role);
                return redirect()->intended('dashboard');
            } else {
                $errorMsg = $response->json()['error'] ?? 'Gagal terhubung ke SAP.';
                Log::warning("[AUTH] Gagal Login SAP user {$username}: {$errorMsg}");
                return back()->withErrors([
                    'username' => 'Login Gagal: ' . $errorMsg,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("[AUTH] Error System saat login user {$username}: " . $e->getMessage());

            return back()->withErrors([
                'username' => 'Terjadi kesalahan sistem. Hubungi IT Support.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        $sessionId = $request->session()->getId();
        
        // Hapus data dari Redis saat logout
        Redis::del("sap_session:{$sessionId}");
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info("[AUTH] User Logout.");

        return redirect('/');
    }
}
