<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\HistoryQualityManagement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\HistorySubmitQm;


class InspectionController extends Controller
{
    public function index(Request $request, $dispo)
    {   
        $plant = $request->query('plant', '3000'); 
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";

        $inspectionLots = [];
        $components = []; 
        $errorMsg = null;
        $sapBaseUrl = config('services.sap.url');

        if (!app()->runningInConsole()) {

        if (Redis::exists($redisKey)) {
            try {
                $decrypted = Crypt::decryptString(Redis::get($redisKey));
                $userData = json_decode($decrypted, true);

                $response = Http::timeout(60)->get("{$sapBaseUrl}/api/get_insp_lot", [
                    'plant'    => $plant,
                    'username' => $userData['username'],
                    'password' => $userData['password'], 
                    'dispo'    => $dispo
                ]);

                if ($response->successful()) {
                    $jsonResponse = $response->json();
                    $inspectionLots = $jsonResponse['data'] ?? [];
                    if (empty($inspectionLots)) {
                        $inspectionLots = $jsonResponse['data_insp_lot'] ?? [];
                    }

                    $components = $jsonResponse['data_components'] ?? [];

                } else {
                    $errorMsg = 'Gagal mengambil data dari SAP. Status: ' . $response->status();
                    Log::error("SAP Error Response: " . $response->body());
                }

            } catch (\Exception $e) {
                Log::error("Error fetching inspection lots: " . $e->getMessage());
                $errorMsg = 'Terjadi kesalahan koneksi ke server SAP.';
            }

        } else {
            return to_route('login')->withErrors(['username' => 'Sesi habis.']);
        }

    } else {

        // Jika dijalankan oleh CLI (artisan, wayfinder, dll)
        // jangan memanggil Redis, Crypt, atau API
        $inspectionLots = [];
        $components = [];
    }

        return Inertia::render('Inspection/List', [
            'initialLots' => $inspectionLots,
            'components'  => $components,
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
        // 1. Setup Session (Sama)
        $sessionId = $request->session()->getId();
        $userData = ['username' => 'Guest', 'nik' => ''];
        $password = '';
        $sapBaseUrl = config('services.sap.url'); 

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
            $response = Http::timeout(60)->get("{$sapBaseUrl}/api/get_insp_lot", [
                'plant'    => $plant,
                'username' => $userData['username'],
                'password' => $password,
                'dispo'    => $dispo
            ]);

            if ($response->successful()) {
                $allLots = $response->json()['data'] ?? []; 
                $targetLot = collect($allLots)->first(function ($lot) use ($lotNumber) {
                    return (int)$lot['PRUEFLOS'] === (int)$lotNumber;
                });
            }
        } catch (\Exception $e) {
            // Silent error
        }
        if (!$targetLot) {
            $targetLot = [
                'PRUEFLOS'   => $lotNumber, // Gunakan nomor dari URL
                'MATNR'      => '-', 
                'KTEXTMAT'   => 'Data lot tidak ditemukan di list API (Cek Log)',
                'CHARG'      => '-',
                'AUFNR'      => null, 
                'WERKS'      => $plant,
                'LOSMENGE'   => 0,
                'MENGENEINH' => 'PC',
                'STATS'      => 'REL',
                'ENSTEHDAT'  => now()->format('Ymd')
            ];
        }
        return Inertia::render('Inspection/Form', [
            'lotNumber' => $lotNumber,
            'authUser'  => $userData,
            'plantCode' => $plant,
            'dispoCode' => $dispo,
            'lotData'   => $targetLot, 
        ]);
    }

    public function getComponents($aufnr)
    {
        try {
            $components = DB::table('production_t_data4')
                ->where('AUFNR', $aufnr)
                ->orderBy('RSPOS', 'asc')
                ->get();
            $components->transform(function ($item) {
                $inspectorInfo = null;

                if (!empty($item->USRISP) && !empty($item->DISPO)) {
                    $userMapping = DB::table('mapping_user_plant')
                        ->where('sap_id', $item->USRISP)
                        ->where('mrp', $item->DISPO) 
                        ->first();

                    if ($userMapping) {
                        $inspectorInfo = [
                            'nik'  => $userMapping->nik,
                            'nama' => $userMapping->nama_karyawan,
                            'sap'  => $userMapping->sap_id
                        ];
                    }
                }

                $item->inspector_details = $inspectorInfo;

                return $item;
            });

            $firstValidInspector = $components->first(function ($item) {
                return !empty($item->inspector_details);
            });

            $historyQm = $firstValidInspector ? $firstValidInspector->inspector_details : null;

            return response()->json([
                'status'  => 'success',
                'data'    => $components,
                'history' => $historyQm
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting components for AUFNR $aufnr: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkPass(Request $request) 
{
    $lotsData = $request->input('lots', []); 
    $requestPlant = $request->input('plant'); 

    // --- VALIDASI PLANT ---
    if (empty($requestPlant)) {
        return response()->json(['status' => 'error', 'message' => 'Plant wajib dikirim.'], 400);
    }
    $udConfig = [];
    if (in_array($requestPlant, ['3000', '1000', '1001'])) {
        $udConfig = ['plant' => '1000', 'ud_code_group' => 'ZI', 'ud_selected_set' => 'Z1', 'ud_code' => 'A'];
    } else if ($requestPlant == '2000') {
        $udConfig = ['plant' => '2000', 'ud_code_group' => 'ZI', 'ud_selected_set' => 'ZI', 'ud_code' => 'A'];
    } else {
        return response()->json(['status' => 'error', 'message' => "Plant {$requestPlant} tidak valid."], 400);
    }

    // --- AMBIL SESI DARI REDIS ---
    $sapUsername = '';
    $sapPassword = '';
    $sapNik      = '';

    try {
        $sessionId = request()->session()->getId();
        $redisKey = "sap_session:{$sessionId}";

        if (Redis::exists($redisKey)) {
            $decrypted = Crypt::decryptString(Redis::get($redisKey));
            $sess = json_decode($decrypted, true);
            $sapUsername = $sess['username'] ?? '';
            $sapPassword = $sess['password'] ?? '';
            $sapNik      = $sess['nik'] ?? ''; 
        }
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Sesi Redis Error.'], 401);
    }

    if (empty($sapUsername) || empty($sapPassword) || empty($sapNik)) {
        return response()->json(['status' => 'error', 'message' => 'Sesi Invalid (NIK/Pass kosong). Login ulang.'], 401);
    }
    
    // --- STREAM RESPONSE ---
    return response()->stream(function () use ($lotsData, $requestPlant, $udConfig, $sapUsername, $sapPassword, $sapNik) {
        $sapBaseUrl = config('services.sap.url'); // Pastikan config ini mengarah ke port Node.js kamu (4003)
        
        foreach ($lotsData as $lotRaw) {
            $lot = (object) $lotRaw; 
            $lotNumber = $lot->PRUEFLOS ?? null;
            if (!$lotNumber) continue; 
            
            $status = 'ERROR';
            $message = 'Unknown Error';
            
            // Flag untuk menentukan apakah boleh lanjut ke UD
            $proceedToUd = true; 

            // --- [BARU] CEK STATUS TECO/REL ---
            $stats = $lot->STATS ?? '';
            // Cek apakah string mengandung TECO dan REL
            $isTecoRel = (strpos($stats, 'TECO') !== false) && (strpos($stats, 'REL') !== false);

            if ($isTecoRel) {
                // Lakukan Un-TECO terlebih dahulu
                try {
                    $untecoPayload = [
                        'username' => $sapUsername,
                        'password' => $sapPassword,
                        'aufnr'    => $lot->AUFNR ?? '' // Pastikan AUFNR ada
                    ];

                    // Panggil API Node.js yang baru kita buat
                    $untecoResponse = Http::timeout(60)->post("{$sapBaseUrl}/api/unteco_production_order", $untecoPayload);
                    $untecoData = $untecoResponse->json();

                    if ($untecoResponse->successful() && ($untecoData['status'] ?? '') == 'success') {
                        // Un-TECO Sukses, lanjut ke UD
                        // Optional: Log keberhasilan Un-TECO jika perlu
                    } else {
                        // Un-TECO Gagal, blokir UD
                        $proceedToUd = false;
                        $status = 'ERROR';
                        $message = "Gagal Un-TECO: " . ($untecoData['message'] ?? 'SAP Error saat Un-TECO');
                    }

                } catch (\Exception $e) {
                    $proceedToUd = false;
                    $status = 'ERROR';
                    $message = "Exception Un-TECO: " . $e->getMessage();
                }
            }
            // --- END LOGIC UN-TECO ---


            // 1. Eksekusi UD ke SAP (Hanya jika proceedToUd TRUE)
            if ($proceedToUd) {
                try {
                    $payload = [
                        'prueflos'        => $lotNumber,
                        'username'        => $sapUsername,
                        'password'        => $sapPassword,
                        'nik'             => $sapNik,
                        'plant'           => $udConfig['plant'], 
                        'ud_selected_set' => $udConfig['ud_selected_set'],
                        'ud_code_group'   => $udConfig['ud_code_group'],
                        'ud_code'         => $udConfig['ud_code'],
                        'stock_posting'   => "X"
                    ];

                    $response = Http::timeout(60)->post("{$sapBaseUrl}/api/send_usage_decision", $payload);
                    $resData = $response->json();

                    if ($response->successful() && ($resData['status'] ?? '') == 'success') {
                        $status = 'SUCCESS';
                        $message = $resData['message'] ?? 'Posted';
                        
                        // Jika tadi melakukan Un-TECO, tambahkan info ke pesan
                        if ($isTecoRel) {
                            $message .= " (Auto Un-TECO Success)";
                        }
                    } else {
                        $status = 'ERROR';
                        $message = $resData['message'] ?? 'SAP Error';
                    }

                } catch (\Exception $e) {
                    $status = 'ERROR';
                    $message = $e->getMessage();
                }
            }

            // 2. Simpan ke Database (History)
            try {
                HistoryQualityManagement::updateOrCreate(
                    ['prueflos' => $lotNumber], 
                    [
                        'plant'              => $requestPlant,
                        'order_number'       => $lot->AUFNR ?? null,
                        'material_code'      => $lot->MATNR ?? null,
                        'material_desc'      => $lot->KTEXTMAT ?? null,
                        'batch'              => $lot->CHARG ?? null,
                        'quantity'           => $lot->LOSMENGE ?? 0,
                        'uom'                => $lot->MENGENEINH ?? null,
                        'sales_order'        => $lot->KDAUF ?? null,
                        'sales_item'         => isset($lot->KDPOS) ? ltrim($lot->KDPOS, '0') : null, 
                        'buyer_name'         => $lot->NAME1 ?? null,
                        'customer_po'        => $lot->BSTNK ?? null,
                        'inspector_sap_id'   => $sapUsername, 
                        'inspector_nik'      => $sapNik,                  
                        'ud_code'            => $udConfig['ud_code'],
                        'ud_selected_set'    => $udConfig['ud_selected_set'],
                        'status'             => $status, 
                        'sap_message'        => $message, 
                        'full_lot_snapshot'  => $lotRaw 
                    ]
                );

            } catch (\Exception $dbEx) {
                Log::error("DB Save Fail Lot {$lotNumber}: " . $dbEx->getMessage());
            }
            
            // 3. Kirim respon streaming ke Client
            echo json_encode([
                'lot' => $lotNumber,
                'status' => $status,
                'message' => $message
            ]) . "\n";

            if (ob_get_level() > 0) ob_flush();
            flush();
        }

        // Tanda Selesai
        echo json_encode(['status' => 'DONE']) . "\n";
        if (ob_get_level() > 0) ob_flush();
        flush();

    }, 200, [
        'Content-Type' => 'application/x-ndjson',
        'X-Accel-Buffering' => 'no',
        'Cache-Control' => 'no-cache',
    ]);
}

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'nik_qc' => 'required',
            'qty_accepted' => 'required|numeric|min:0',
            'qty_reject'   => 'required|numeric|min:0',
            'defects'      => 'nullable|array',
            'cause_effect' => 'nullable|string',
            'correction'   => 'nullable|string',
            
            // Validasi gambar (array string base64)
            'images'       => 'nullable|array',
            'images.front' => 'nullable|string',
            'images.back'  => 'nullable|string',
            'images.top'   => 'nullable|string',
            'images.bottom'=> 'nullable|string',

            // Data AQL
            'aql_critical_found' => 'nullable|numeric',
            // ... tambahkan validasi lain sesuai kebutuhan
        ]);

        // 2. Ambil Sesi SAP (Untuk Username/Pass pengirim)
        $sessionId = $request->session()->getId();
        $redisKey = "sap_session:{$sessionId}";
        $sapCreds = [];

        if (Redis::exists($redisKey)) {
            $decrypted = Crypt::decryptString(Redis::get($redisKey));
            $sapCreds = json_decode($decrypted, true);
        } else {
            return redirect()->back()->withErrors(['message' => 'Sesi SAP habis, silakan login ulang.']);
        }

        DB::beginTransaction();
        try {
            // 3. Proses Upload Gambar (Base64 ke File Fisik)
            $savedImages = [];
            $lotNumber = $request->input('lot_number', 'unknown'); // Pastikan Vue kirim lot_number jika ada, atau ambil dari sesi/url
            
            foreach ($request->input('images', []) as $side => $base64String) {
                if (!empty($base64String)) {
                    // Hapus header base64 (data:image/jpeg;base64,...)
                    $imageParts = explode(";base64,", $base64String);
                    if (count($imageParts) >= 2) {
                        $imageTypeAux = explode("image/", $imageParts[0]);
                        $imageType = $imageTypeAux[1] ?? 'jpeg';
                        $imageBase64 = base64_decode($imageParts[1]);
                        
                        // Buat nama file unik
                        $fileName = "inspection/{$lotNumber}_{$side}_" . time() . ".{$imageType}";
                        
                        // Simpan ke Storage (public/inspection/...)
                        Storage::disk('public')->put($fileName, $imageBase64);
                        
                        $savedImages[$side] = $fileName;
                    }
                }
            }

            // 4. Hitung UD Code (Otomatis logic sederhana)
            // Logic: Jika Reject > 0 maka 'R' (Reject), jika 0 maka 'A' (Accept)
            // Sesuaikan dengan logic bisnis plant Anda (seperti di bulkPass)
            $udCode = ($validated['qty_reject'] > 0) ? 'R' : 'A';
            
            // 5. Kirim ke SAP (Opsional: Menggunakan endpoint send_usage_decision seperti bulkPass)
            $sapBaseUrl = config('services.sap.url');
            
            // NOTE: Sesuaikan logic Plant/UD Set seperti di function bulkPass Anda
            // Contoh sederhana:
            $payloadSAP = [
                'prueflos'        => $lotNumber, 
                'username'        => $sapCreds['username'],
                'password'        => $sapCreds['password'],
                'nik'             => $sapCreds['nik'],
                'plant'           => '1000', // Sesuaikan logika dynamic plant
                'ud_code'         => $udCode,
                'ud_selected_set' => 'Z1',   // Sesuaikan
                'ud_code_group'   => 'ZI',   // Sesuaikan
                'stock_posting'   => 'X'
            ];

            // Uncomment jika ingin langsung kirim ke SAP saat klik simpan
            /*
            $responseSAP = Http::timeout(60)->post("{$sapBaseUrl}/api/send_usage_decision", $payloadSAP);
            if (!$responseSAP->successful()) {
                throw new \Exception("Gagal kirim ke SAP: " . $responseSAP->body());
            }
            */

            // 6. Simpan ke Database Lokal (HistoryQualityManagement atau tabel InspectionDetail)
            // Disini saya contohkan simpan ke HistoryQualityManagement seperti bulkPass
            HistoryQualityManagement::create([
                'prueflos'       => $lotNumber,
                'inspector_nik'  => $validated['nik_qc'],
                'quantity'       => $validated['qty_accepted'], // Total atau accepted?
                'status'         => 'SUCCESS', // Atau 'PENDING'
                'sap_message'    => 'Manual Inspection Input',
                'ud_code'        => $udCode,
                // Simpan data detail inspection (Defect, Notes, Images) ke kolom JSON atau tabel terpisah
                'full_lot_snapshot' => json_encode([
                    'defects'      => $validated['defects'],
                    'cause_effect' => $validated['cause_effect'],
                    'correction'   => $validated['correction'],
                    'images'       => $savedImages,
                    'aql'          => [
                        'critical' => $request->input('aql_critical_found'),
                        'major'    => $request->input('aql_major_found'),
                        'minor'    => $request->input('aql_minor_found'),
                    ]
                ])
            ]);

            DB::commit();

            // 7. Redirect kembali
            return redirect()->back()->with('success', 'Data inspeksi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing inspection: " . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    public function simulate(Request $request)
    {
        $validated = $request->validate([
            'pro' => [
                'nullable', 
                'string', 
                'regex:/^[0-9; ]+$/', // Allow spaces
                'required_without:mrp'
            ],
            'mrp' => [
                'nullable', 
                'string',
                'required_without:pro'
            ],
        ], [
            'pro.regex' => 'Format PRO tidak valid. Hanya angka, spasi, dan titik koma (;) yang diperbolehkan.',
            'pro.required_without' => 'PRO wajib diisi jika MRP kosong.',
            'mrp.required_without' => 'MRP wajib diisi jika PRO kosong.',
        ]);

        if (!empty($validated['pro']) && !empty($validated['mrp'])) {
            return back()->withErrors(['message' => 'Hanya boleh mengisi salah satu (PRO atau MRP).']);
        }

        // Parse Data & Construct Payload
        $payload = [
            'username' => env('SAP_API_USERNAME', 'auto_email'),
            'password' => env('SAP_API_PASSWORD', '11223344'), 
            'values'   => []
        ];
        $typeLabel = '';

        if (!empty($validated['pro'])) {
            $payload['type'] = 'PRO';
            $typeLabel = 'Production Order (PRO)';
            // Replace spaces with semicolons, then split by semicolon
            $raw = str_replace(' ', ';', $validated['pro']);
            $items = explode(';', $raw);
            $payload['values'] = array_values(array_filter($items, fn($value) => !is_null($value) && trim($value) !== ''));
        } else {
            $payload['type'] = 'MRP';
            $typeLabel = 'MRP Controller';
            $raw = str_replace(' ', ';', $validated['mrp']);
            $items = explode(';', $raw);
            $payload['values'] = array_values(array_filter($items, fn($value) => !is_null($value) && trim($value) !== ''));
        }

        try {
            $apiUrl = env('SAP_API_URL') . '/api/get_data_inspect_oper';
            $response = Http::timeout(3600)->post($apiUrl, $payload); // Long timeout for MRP

            if ($response->successful()) {
                $json = $response->json();
                
                if (($json['status'] ?? '') === 'success') {
                    $mappedData = collect($json['data'] ?? [])->map(function ($item) {
                        return [
                            'PERNR' => $item['PERNR'] ?? '-',
                            'AUFNR' => $item['AUFNR'] ?? '-',
                            'MAKTX' => $item['MAKTX'] ?? '-',
                            'QTY'   => (float)($item['GMNGA'] ?? 0),
                            'UOM'   => ($item['GMEIN'] ?? '') === 'ST' ? 'PC' : ($item['GMEIN'] ?? ''),
                            'BUDAT' => isset($item['BUDAT']) ? date('d-m-Y', strtotime($item['BUDAT'])) : '-',
                            // Hidden fields
                            'RUECK' => $item['RUECK'] ?? null,
                            'RMZHL' => $item['RMZHL'] ?? null
                        ];
                    });

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data ditemukan.',
                        'data' => [
                            'type'  => $typeLabel,
                            'count' => $mappedData->count(),
                            'items' => $mappedData
                        ]
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'API SAP Error: ' . ($json['message'] ?? 'Unknown Error'),
                        'errors' => $json['errors'] ?? null
                    ], 400);
                }

            } else {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Gagal menghubungi server SAP. HTTP ' . $response->status()
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error("Inspection Simulation Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
    public function submitQm(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.rueck' => 'required',
            'items.*.rmzhl' => 'required',
        ]);

        $sessionId = $request->session()->getId();
        $sapCreds = [];
        if (Redis::exists("sap_session:{$sessionId}")) {
            $decrypted = Crypt::decryptString(Redis::get("sap_session:{$sessionId}"));
            $sapCreds = json_decode($decrypted, true);
        }

        $username = $sapCreds['username'] ?? 'unknown';
        $password = $sapCreds['password'] ?? '';
        $nik = $sapCreds['nik'] ?? null;
        
        $namaKaryawan = null;
        if ($nik) {
             $emp = DB::table('mapping_user_plant')->where('nik', $nik)->first();
             $namaKaryawan = $emp ? $emp->nama_karyawan : null;
        }
        
        $payload = [
            'items' => $request->input('items'),
            'username' => $username,
            'password' => $password
        ];

        return response()->stream(function () use ($payload, $username, $nik, $namaKaryawan) {
             $sapBaseUrl = config('services.sap.url'); 
             $url = "{$sapBaseUrl}/api/submit_qm_stream";

             $client = new \GuzzleHttp\Client();
             try {
                 $response = $client->post($url, [
                     'json' => $payload,
                     'stream' => true,
                     'timeout' => 300 
                 ]);
                 
                 $body = $response->getBody();
                 $buffer = '';

                 while (!$body->eof()) {
                     $chunk = $body->read(1024);
                     $buffer .= $chunk;
                     
                     while (($newline = strpos($buffer, "\n")) !== false) {
                         $line = substr($buffer, 0, $newline);
                         $buffer = substr($buffer, $newline + 1);
                         
                         if (trim($line)) {
                             $data = json_decode($line, true);
                             
                             // Log to DB
                             if (isset($data['rueck'])) {
                                  try {
                                      // Extract AUFNR/MAKTX/BUDAT from SAP Data if available
                                      // Assuming T_DATA1 (data) is an array of objects
                                      $sapData = $data['data'] ?? [];
                                      if (is_array($sapData) && count($sapData) > 0) {
                                          $sapItem = $sapData[0]; // Take first item
                                      } else {
                                          $sapItem = [];
                                      }

                                      HistorySubmitQm::create([
                                          'username' => $username,
                                          'nik' => $nik,
                                          'nama_karyawan' => $namaKaryawan,
                                          'process_date' => now(),
                                          'aufnr' => $sapItem['AUFNR'] ?? null,
                                          'maktx' => $sapItem['MAKTX'] ?? null,
                                          'rueck' => $data['rueck'] ?? null,
                                          'rmzhl' => $data['rmzhl'] ?? null,
                                          'budat' => isset($sapItem['BUDAT']) ? date('Y-m-d', strtotime($sapItem['BUDAT'])) : null,
                                          'status' => $data['status'] ?? 'unknown',
                                          'message' => $data['message'] ?? ''
                                      ]);
                                  } catch (\Exception $e) {
                                      Log::error("Failed to log submit QM: " . $e->getMessage());
                                  }
                             }

                             echo $line . "\n";
                             if (ob_get_level() > 0) ob_flush();
                             flush();
                         }
                     }
                 }
             } catch (\Exception $e) {
                 echo json_encode(['status' => 'critical_error', 'message' => $e->getMessage()]) . "\n";
                 Log::error("Stream Proxy Error: " . $e->getMessage());
             }

             // Tanda Selesai
             echo json_encode(['status' => 'DONE']) . "\n";
             if (ob_get_level() > 0) ob_flush();
             flush();

        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
