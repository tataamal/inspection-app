<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
            'nik'      => ['required'],
        ]);

        $username = $credentials['username'];
        Log::info("[AUTH] Percobaan login user: {$username}");

        try {
            $response = Http::timeout(30)->post('http://127.0.0.1:4003/api/sap-login', [
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ]);

            if ($response->successful()) {
                Log::info("[AUTH] Login SAP Berhasil untuk user: {$username}");
                $request->session()->regenerate();
                $sessionId = $request->session()->getId();
                $redisKey = "sap_session:{$sessionId}";
                $sapData = [
                    'username' => $credentials['username'],
                    'password' => $credentials['password'],
                    'nik'      => $credentials['nik'],
                    'sap_status' => 'connected'
                ];
                Redis::setex($redisKey, 7200, Crypt::encryptString(json_encode($sapData)));
                $request->session()->put('user_nik', $credentials['nik']);
                $request->session()->put('user_sap_id', $credentials['username']);
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
