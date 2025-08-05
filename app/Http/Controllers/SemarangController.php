<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SemarangController extends Controller
{
    public function InspectionLotSemarang()
    {
        return $this->inspection_lot('3000', 'Semarang.pro');
    }

    public function inspection_lot($plant, $view)
    {
        try {

            $credential = [
                'username' => session('sap_user'),
                'password' => session('sap_password')

            ];
            // Ambil URL Flask API dari .env, default ke localhost jika belum diset
            $flaskBaseUrl = env('FLASK_API_URL', 'http://127.0.0.1:5050');

            // Call Flask API untuk update data dari SAP
            $response = Http::timeout(60)->get("{$flaskBaseUrl}/api/get_insp_lot", [
                'username' => $credential['username'],
                'password' => $credential['password'],
                'plant' => $plant
            ]);

            // Log response jika gagal
            if (!$response->successful()) {
                Log::error("Gagal fetch dari Flask (Plant $plant): " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Error saat memanggil Flask untuk plant $plant: " . $e->getMessage());
        }

        // Apapun hasilnya, kita ambil data dari MySQL (cache terakhir)
        $data = DB::table('quality_inspection_lots')
            ->where('WERK', $plant)
            ->whereNot('ART', '10')
            ->get();

        return view($view, [
            'data' => $data,
            'error' => $data->isEmpty() ? 'Data kosong atau gagal memuat dari SAP' : null
        ]);
    }

    public function TaskDOSemarang()
    {
        $data = DB::table('quality_inspection_lots')
            ->where('WERK', '3000')
            ->where('ART', '10')
            ->get();

        return view('semarang.do', [
            'data' => $data,
            'error' => $data->isEmpty() ? 'Data kosong atau gagal memuat dari SAP' : null
        ]);
    }

    public function ActionButton($aufnr)
    {
        $aufnrPadded = str_pad($aufnr, 10, '0', STR_PAD_LEFT);

        $data = DB::table('quality_inspection_lots') 
        ->where('AUFNR', $aufnr)
        ->orWhere('AUFNR', $aufnrPadded)
        ->first();
        
        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view('semarang.inspection', compact('data'));
    }
}
