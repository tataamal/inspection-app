<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CheckSapSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->session()->getId();
        
        // Cek apakah ada data user di Redis
        if (!Redis::exists("sap_session:{$sessionId}")) {
            // Jika tidak ada, tendang ke halaman login
            return redirect('/')->withErrors(['username' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
        }

        return $next($request);
    }
}
