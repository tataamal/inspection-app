<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    public function Login(Request $request)
    {
        // dd($request);
        $response = Http::post('http://localhost:5050/api/sap-login',[
            'username' => $request->username,
            'password'=> $request->password
        ]);

        // dd($response);

        if ($response->json('status') === 'connected') {
            $UserData = $response->json();

            $User = User::create($UserData);

            // Simpan ke session (langsung dari hasil Flask)
            Session::put('username', $UserData['username']);
            Session::put('password', $UserData['password']);
            Session::put('is_logged_in_sap', true);

            return redirect()->route('region')->with('success', 'Login SAP berhasil!');
        }
    }
}
