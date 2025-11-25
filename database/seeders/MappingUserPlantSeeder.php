<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MappingUserPlant;
use Illuminate\Support\Facades\DB;

class MappingUserPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataKaryawan = [
            ["Plant"=>"1001","Mrp"=>"WW1","NIK"=>"10000096","Nama karyawan"=>"Choirul Rohman"],
            ["Plant"=>"1001","Mrp"=>"WW2","NIK"=>"10000207","Nama karyawan"=>"Badar riyatmono"],
            ["Plant"=>"1001","Mrp"=>"WW3","NIK"=>"10000123","Nama karyawan"=>"Candra Setiawan"],
            ["Plant"=>"1001","Mrp"=>"WW4","NIK"=>"10000113","Nama karyawan"=>"Itsna Yuli Choirurrohman"],
            ["Plant"=>"1000","Mrp"=>"WE1","NIK"=>"10000164","Nama karyawan"=>"Bambang Prayitno"],
            ["Plant"=>"1000","Mrp"=>"WE2","NIK"=>"10000164","Nama karyawan"=>"Bambang Prayitno"],
            ["Plant"=>"1000","Mrp"=>"WM1","NIK"=>"10000029","Nama karyawan"=>"Mas Arif Rumadi"],
            ["Plant"=>"1000","Mrp"=>"PN1","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"PN2","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"PN3","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"VN1","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"VN2","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"PV1","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"1000","Mrp"=>"PV2","NIK"=>"10000020","Nama karyawan"=>"Muhammad Syaroni"],
            ["Plant"=>"2000","Mrp"=>"GA1","NIK"=>"10000424","Nama karyawan"=>"M.ZAINUDDIN"],
            ["Plant"=>"2000","Mrp"=>"GA1","NIK"=>"10000432","Nama karyawan"=>"NUR SALIM"],
            ["Plant"=>"2000","Mrp"=>"GA2","NIK"=>"10000424","Nama karyawan"=>"M.ZAINUDDIN"],
            ["Plant"=>"2000","Mrp"=>"GA2","NIK"=>"10000432","Nama karyawan"=>"NUR SALIM"],
            ["Plant"=>"2000","Mrp"=>"GD1","NIK"=>"10000505","Nama karyawan"=>"PURWANTO"],
            ["Plant"=>"2000","Mrp"=>"GD1","NIK"=>"10000437","Nama karyawan"=>"SUPRIANTO"],
            ["Plant"=>"2000","Mrp"=>"GD2","NIK"=>"10000505","Nama karyawan"=>"PURWANTO"],
            ["Plant"=>"2000","Mrp"=>"GD2","NIK"=>"10000437","Nama karyawan"=>"SUPRIANTO"],
            ["Plant"=>"2000","Mrp"=>"EB2","NIK"=>"10000471","Nama karyawan"=>"SIGIT WIDODO"],
            ["Plant"=>"2000","Mrp"=>"C11","NIK"=>"10000487","Nama karyawan"=>"Hariyono"],
            ["Plant"=>"2000","Mrp"=>"C12","NIK"=>"10000429","Nama karyawan"=>"Beni Sunarko"],
            ["Plant"=>"2000","Mrp"=>"RD2","NIK"=>"10000644","Nama karyawan"=>"AGUS SUPRIYANTO"],
            ["Plant"=>"2000","Mrp"=>"RD3","NIK"=>"10000427","Nama karyawan"=>"ERWIN MOHTAR AFFANDI"],
            ["Plant"=>"2000","Mrp"=>"RD4","NIK"=>"10000615","Nama karyawan"=>"MUCHAMAD AL-AMIN"],
            ["Plant"=>"2000","Mrp"=>"CH1","NIK"=>"10000526","Nama karyawan"=>"Andri Wahyu Pradana"],
            ["Plant"=>"2000","Mrp"=>"CH2","NIK"=>"10000466","Nama karyawan"=>"Agus Suherianto"],
            ["Plant"=>"2000","Mrp"=>"CH4","NIK"=>"10000428","Nama karyawan"=>"hari susanto"],
            ["Plant"=>"2000","Mrp"=>"CH5","NIK"=>"10003386","Nama karyawan"=>"CATUR HERI P"],
            ["Plant"=>"2000","Mrp"=>"CH7","NIK"=>"10000466","Nama karyawan"=>"Agus Suherianto"],
            ["Plant"=>"2000","Mrp"=>"CH8","NIK"=>"10000466","Nama karyawan"=>"Agus Suherianto"],
            ["Plant"=>"2000","Mrp"=>"CH9","NIK"=>"10000466","Nama karyawan"=>"Agus Suherianto"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000388","Nama karyawan"=>"Abd Kholiq Idris"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000587","Nama karyawan"=>"Moch Darmawan Eko P"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000544","Nama karyawan"=>"Mochamad Choirudin"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000413","Nama karyawan"=>"Arif Budi Prasetya"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000410","Nama karyawan"=>"Dian Prasetyo"],
            ["Plant"=>"2000","Mrp"=>"GF1","NIK"=>"10000544","Nama karyawan"=>"Sunarto"],
            ["Plant"=>"2000","Mrp"=>"GF2","NIK"=>"10000881","Nama karyawan"=>"Saiful Mutohar"],
            ["Plant"=>"2000","Mrp"=>"GF2","NIK"=>"10000661","Nama karyawan"=>"Angga Setyawan"],
            ["Plant"=>"2000","Mrp"=>"GF2","NIK"=>"10000619","Nama karyawan"=>"Eko Setiawan"],
            ["Plant"=>"2000","Mrp"=>"MF1","NIK"=>"10000045","Nama karyawan"=>"SURANTO"],
            ["Plant"=>"2000","Mrp"=>"MF2","NIK"=>"10000045","Nama karyawan"=>"SURANTO"],
            ["Plant"=>"2000","Mrp"=>"MF3","NIK"=>"10000045","Nama karyawan"=>"Suranto"],
            ["Plant"=>"2000","Mrp"=>"MF4","NIK"=>"10000045","Nama karyawan"=>"Suranto"],
            ["Plant"=>"2000","Mrp"=>"CP1","NIK"=>"10002225","Nama karyawan"=>"Risma Choirun Nissa"],
            ["Plant"=>"2000","Mrp"=>"CP2","NIK"=>"10002225","Nama karyawan"=>"Risma Choirun Nissa"],
            ["Plant"=>"2000","Mrp"=>"CP3","NIK"=>"10002225","Nama karyawan"=>"Risma Choirun Nissa"],
            ["Plant"=>"2000","Mrp"=>"CSK","NIK"=>"10002225","Nama karyawan"=>"Risma Choirun Nissa"],
            ["Plant"=>"3000","Mrp"=>"D22","NIK"=>"10002069","Nama karyawan"=>null],
            ["Plant"=>"3000","Mrp"=>"PG2","NIK"=>"10002260","Nama karyawan"=>null],
            ["Plant"=>"3000","Mrp"=>"MW1","NIK"=>"10000900","Nama karyawan"=>null],
            ["Plant"=>"3000","Mrp"=>"MW2","NIK"=>"10000900","Nama karyawan"=>null],
            ["Plant"=>"3000","Mrp"=>"MW3","NIK"=>"10000900","Nama karyawan"=>null],
            ["Plant"=>"3000","Mrp"=>"D21","NIK"=>"10002555","Nama karyawan"=>null]
        ];

        // Mulai Transaksi agar lebih cepat
        DB::beginTransaction();
        
        try {
            foreach ($dataKaryawan as $row) {
                MappingUserPlant::create([
                    'plant'         => $row['Plant'],
                    'mrp'           => $row['Mrp'],
                    'nik'           => $row['NIK'],
                    'nama_karyawan' => $row['Nama karyawan'],
                    'sap_id'        => null,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
