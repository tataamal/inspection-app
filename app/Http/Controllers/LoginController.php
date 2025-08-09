<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    public function Login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:admin,inspector,buyer', // Pastikan role valid
        ]);

        // dd($request);

        $credentials = $request->only('username', 'password', 'role');
        // dd($credentials);

        // Cek autentikasi Laravel (MySQL)
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user(); // Get the logged-in user
            $role = $user->role;

            // dd($user, $role);

            // Jika role cocok untuk login ke SAP
            if (in_array($role, ['admin', 'inspector'])) {
                // Ambil SAP credential dari form (boleh pakai input khusus jika SAP credential beda dari Laravel)
                $sapUsername = $request->input('username');
                $sapPassword = $request->input('password');

                // dd($sapUsername, $sapPassword);

                // Kirim request ke API Flask
                $response = Http::post('http://localhost:5050/api/sap-login', [
                    'username' => $sapUsername,
                    'password' => $sapPassword,
                ]);

                // dd($response->json(), $role);

                // Jika login ke SAP berhasil
                if ($response->json('status') === 'connected') {
                    // Simpan ke session
                    session([
                        'sap_user' => $sapUsername,
                        'sap_password' => $sapPassword,
                        'user_role' => $role,
                    ]);

                    // dd(session()->all());

                    // Redirect sesuai role
                    return match ($role) {
                        'admin'     => redirect()->route('dashboard'),
                        'inspector' => redirect()->route('dashboard'),
                        'buyer'     => redirect()->route('dashboardd'),
                        default     => redirect()->route('dashboard'),
                    };
                } else {
                    // Gagal login SAP
                    Auth::logout(); // logout dari Laravel juga
                    return back()->withErrors([
                        'sap' => 'Username atau Password SAP tidak sesuai.',
                    ])->onlyInput('username');
                }
            } else {
                // Role tidak perlu login SAP, langsung redirect
                return redirect()->route('home');
            }
        }

        // Gagal login Laravel
        return back()->withErrors([
            'username' => 'Username atau Password salah.',
        ])->onlyInput('username');
    }

    public function index()
    {
        return view('index');
    }

    public function ErrorsPage()
    {
        return view('errors.404');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
