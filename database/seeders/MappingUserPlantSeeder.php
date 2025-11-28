<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MappingUserPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('document/mapping_user_qm.csv');

        if (!file_exists($filePath)) {
            $this->command->error("File tidak ditemukan di: $filePath");
            $this->command->line("Silakan letakkan file CSV 'mapping_user_qm.csv' di folder 'public/document/' terlebih dahulu.");
            return;
        }

        $this->command->info("Membaca file CSV dari: $filePath ...");

        $dataToInsert = [];
        $now = Carbon::now();
        $batchSize = 500;

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $firstRow = true;

            while (($row = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($firstRow) {
                    $firstRow = false;
                    continue;
                }

                $dataToInsert[] = [
                    'plant'         => $row[1] ?? null,
                    'mrp'           => $row[2] ?? null,
                    'nik'           => $row[3] ?? null,
                    'nama_karyawan' => $row[4] ?? null,
                    'sap_id'        => $row[5] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                if (count($dataToInsert) >= $batchSize) {
                    DB::table('mapping_user_plant')->insert($dataToInsert);
                    $dataToInsert = [];
                }
            }
            fclose($handle);
        }
        if (!empty($dataToInsert)) {
            DB::table('mapping_user_plant')->insert($dataToInsert);
        }

        $this->command->info("Seeder berhasil dijalankan!");
    }
}