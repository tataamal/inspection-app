<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MappingUserPlant; // Pastikan Model ini ada
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MappingUserPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $dataKaryawan = [
            ["plant"=>"1001","mrp"=>"WW1","nik"=>"10000096","nama_karyawan"=>"Choirul Rohman","sap_id"=>"KMI-U081"],
            ["plant"=>"1001","mrp"=>"WW2","nik"=>"10000207","nama_karyawan"=>"Badar riyatmono","sap_id"=>"KMI-U082"],
            ["plant"=>"1001","mrp"=>"WW3","nik"=>"10000123","nama_karyawan"=>"Candra Setiawan","sap_id"=>"KMI-U081"],
            ["plant"=>"1001","mrp"=>"WW4","nik"=>"10000113","nama_karyawan"=>"Itsna Yuli Choirurrohman","sap_id"=>"KMI-U081"], // Spasi dihapus
            ["plant"=>"1000","mrp"=>"WE1","nik"=>"10000164","nama_karyawan"=>"Bambang Prayitno","sap_id"=>"KMI-U086"],
            ["plant"=>"1000","mrp"=>"WE2","nik"=>"10000164","nama_karyawan"=>"Bambang Prayitno","sap_id"=>"KMI-U086"],
            ["plant"=>"1000","mrp"=>"WM1","nik"=>"10000029","nama_karyawan"=>"Mas Arif Rumadi","sap_id"=>"KMI-U165"],
            ["plant"=>"1000","mrp"=>"PN1","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U083"],
            ["plant"=>"1000","mrp"=>"PN2","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U110"],
            ["plant"=>"1000","mrp"=>"PN3","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U110"],
            ["plant"=>"1000","mrp"=>"VN1","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U097"],
            ["plant"=>"1000","mrp"=>"VN2","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U097"],
            ["plant"=>"1000","mrp"=>"PV1","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U097"],
            ["plant"=>"1000","mrp"=>"PV2","nik"=>"10000020","nama_karyawan"=>"Muhammad Syaroni","sap_id"=>"KMI-U097"],
            ["plant"=>"2000","mrp"=>"GA1","nik"=>"10000424","nama_karyawan"=>"M.ZAINUDDIN","sap_id"=>"KMI-U088"],
            ["plant"=>"2000","mrp"=>"GA1","nik"=>"10000432","nama_karyawan"=>"NUR SALIM","sap_id"=>"KMI-U088"],
            ["plant"=>"2000","mrp"=>"GA2","nik"=>"10000424","nama_karyawan"=>"M.ZAINUDDIN","sap_id"=>"KMI-U088"],
            ["plant"=>"2000","mrp"=>"GA2","nik"=>"10000432","nama_karyawan"=>"NUR SALIM","sap_id"=>"KMI-U088"],
            ["plant"=>"2000","mrp"=>"GD1","nik"=>"10000505","nama_karyawan"=>"PURWANTO","sap_id"=>"KMI-U085"],
            ["plant"=>"2000","mrp"=>"GD1","nik"=>"10000437","nama_karyawan"=>"SUPRIANTO","sap_id"=>"KMI-U085"],
            ["plant"=>"2000","mrp"=>"GD2","nik"=>"10000505","nama_karyawan"=>"PURWANTO","sap_id"=>"KMI-U085"],
            ["plant"=>"2000","mrp"=>"GD2","nik"=>"10000437","nama_karyawan"=>"SUPRIANTO","sap_id"=>"KMI-U085"],
            ["plant"=>"2000","mrp"=>"EB2","nik"=>"10000471","nama_karyawan"=>"SIGIT WIDODO","sap_id"=>"KMI-U088"],
            ["plant"=>"2000","mrp"=>"C11","nik"=>"10000487","nama_karyawan"=>"Hariyono","sap_id"=>"KMI-U098"],
            ["plant"=>"2000","mrp"=>"C12","nik"=>"10000429","nama_karyawan"=>"Beni Sunarko","sap_id"=>"KMI-U098"],
            ["plant"=>"2000","mrp"=>"RD2","nik"=>"10000644","nama_karyawan"=>"AGUS SUPRIYANTO","sap_id"=>"KMI-U100"],
            ["plant"=>"2000","mrp"=>"RD3","nik"=>"10000427","nama_karyawan"=>"ERWIN MOHTAR AFFANDI","sap_id"=>"KMI-U100"],
            ["plant"=>"2000","mrp"=>"RD4","nik"=>"10000615","nama_karyawan"=>"MUCHAMAD AL-AMIN","sap_id"=>"KMI-U100"],
            ["plant"=>"2000","mrp"=>"CH1","nik"=>"10000526","nama_karyawan"=>"Andri Wahyu Pradana","sap_id"=>"KMI-U090"],
            ["plant"=>"2000","mrp"=>"CH2","nik"=>"10000466","nama_karyawan"=>"Agus Suherianto","sap_id"=>"KMI-U090"],
            ["plant"=>"2000","mrp"=>"CH4","nik"=>"10000428","nama_karyawan"=>"hari susanto","sap_id"=>"KMI-U091"],
            ["plant"=>"2000","mrp"=>"CH5","nik"=>"10003386","nama_karyawan"=>"CATUR HERI P","sap_id"=>"KMI-U091"],
            ["plant"=>"2000","mrp"=>"CH7","nik"=>"10000466","nama_karyawan"=>"Agus Suherianto","sap_id"=>"KMI-U091"],
            ["plant"=>"2000","mrp"=>"CH8","nik"=>"10000466","nama_karyawan"=>"Agus Suherianto","sap_id"=>"KMI-U090"],
            ["plant"=>"2000","mrp"=>"CH9","nik"=>"10000466","nama_karyawan"=>"Agus Suherianto","sap_id"=>"KMI-U090"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000388","nama_karyawan"=>"Abd Kholiq Idris","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000587","nama_karyawan"=>"Moch Darmawan Eko P","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000544","nama_karyawan"=>"Mochamad Choirudin","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000413","nama_karyawan"=>"Arif Budi Prasetya","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000410","nama_karyawan"=>"Dian Prasetyo","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF1","nik"=>"10000544","nama_karyawan"=>"Sunarto","sap_id"=>"KMI-U095"],
            ["plant"=>"2000","mrp"=>"GF2","nik"=>"10000881","nama_karyawan"=>"Saiful Mutohar","sap_id"=>"KMI-U096"],
            ["plant"=>"2000","mrp"=>"GF2","nik"=>"10000661","nama_karyawan"=>"Angga Setyawan","sap_id"=>"KMI-U096"],
            ["plant"=>"2000","mrp"=>"GF2","nik"=>"10000619","nama_karyawan"=>"Eko Setiawan","sap_id"=>"KMI-U096"],
            ["plant"=>"2000","mrp"=>"MF1","nik"=>"10000045","nama_karyawan"=>"SURANTO","sap_id"=>"KMI-U115"],
            ["plant"=>"2000","mrp"=>"MF2","nik"=>"10000045","nama_karyawan"=>"SURANTO","sap_id"=>"KMI-U129"],
            ["plant"=>"2000","mrp"=>"MF3","nik"=>"10000045","nama_karyawan"=>"Suranto","sap_id"=>"KMI-U115"],
            ["plant"=>"2000","mrp"=>"MF4","nik"=>"10000045","nama_karyawan"=>"Suranto","sap_id"=>"KMI-U141"],
            ["plant"=>"2000","mrp"=>"CP1","nik"=>"10002225","nama_karyawan"=>"Risma Choirun Nissa","sap_id"=>"KMI-U114"],
            ["plant"=>"2000","mrp"=>"CP2","nik"=>"10002225","nama_karyawan"=>"Risma Choirun Nissa","sap_id"=>"KMI-U114"],
            ["plant"=>"2000","mrp"=>"CP3","nik"=>"10002225","nama_karyawan"=>"Risma Choirun Nissa","sap_id"=>"KMI-U114"],
            ["plant"=>"2000","mrp"=>"CSK","nik"=>"10002225","nama_karyawan"=>"Risma Choirun Nissa","sap_id"=>"KMI-U114"],
            ["plant"=>"2000","mrp"=>"GT1","nik"=>"10000074","nama_karyawan"=>"Risma Choirun Nissa","sap_id"=>"KMI-U121"],
            ["plant"=>"3000","mrp"=>"D22","nik"=>"10002069","nama_karyawan"=>"BENIYANTO","sap_id"=>"KMI-U128"], 
            ["plant"=>"3000","mrp"=>"PG2","nik"=>"10002260","nama_karyawan"=>"Mustolihul Hasan","sap_id"=>"KMI-U143"],
            ["plant"=>"3000","mrp"=>"MW1","nik"=>"10000900","nama_karyawan"=>"Mursalin","sap_id"=>"KMI-U132"],
            ["plant"=>"3000","mrp"=>"MW2","nik"=>"10000900","nama_karyawan"=>"Mursalin","sap_id"=>"KMI-U132"],
            ["plant"=>"3000","mrp"=>"MW3","nik"=>"10000900","nama_karyawan"=>"Mursalin","sap_id"=>"KMI-U132"],
            ["plant"=>"3000","mrp"=>"D21","nik"=>"10002555","nama_karyawan"=>"Heri Prima Setiawan","sap_id"=>"KMI-U117"]
        ];

        // Saya juga mengubah key array di atas menjadi huruf kecil semua (plant, mrp, dst) 
        // agar sesuai langsung dengan nama kolom database. Jika Anda pakai MappingUserPlant::insert(), 
        // nama key HARUS persis dengan nama kolom database.

        // Tambahkan timestamp untuk setiap row (karena insert bulk tidak otomatis menambahkannya)
        $dataToInsert = array_map(function($item) use ($now) {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $dataKaryawan);

        // Gunakan insert() agar lebih performant (hanya 1 query ke DB)
        MappingUserPlant::insert($dataToInsert);
    }
}