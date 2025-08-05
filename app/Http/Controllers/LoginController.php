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
        // dd($request);
        $credentials = $request->only('username', 'password');

        // dd($credentials);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // dd($request);

            $role = Auth::user()->role;

            // dd($role);

            // masuk ke SAP
            if(in_array($role, ['admin', 'inspector'])) {
                // minta SAP credential
                $sapUsername = $request->input('username');
                $sapPassword = $request->input('password');
                $userRole = $request->input('role');

                // dd($sapUsername, $sapPassword);

                // autentikasi ke SAP menggunakan API Flask
                $response = Http::post('http://localhost:5050/api/sap-login',[
                    'username' => $sapUsername,
                    'password'=> $sapPassword,
                ]);

                // dd($response->body());

                if($response->json('status') === 'connected') {
                    // simpan credential ke session
                    session([
                        'sap_user' => $sapUsername,
                        'sap_password' => $sapPassword,
                        'user_role' => $userRole,
                    ]);

                    return match ($role) {
                        'admin' => view('dashboard',[
                            compact('$totalInspection')
                        ]),
                        'inspector' => view('dashboard'),
                        'buyer' => view('dashboard'),
                    };

                } else {
                    return back()->withErrors(['sap' => 'Login ke SAP gagal.'])->onlyInput('username');
                }
            } else {
                dd('Tidak masuk ke SAP');
            }
        }
        
        return back()->withErrors([
            'username' => 'Username atau Password salah. ',
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
