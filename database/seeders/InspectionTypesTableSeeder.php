<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InspectionType;

class InspectionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inspectionTypes = [
            ['name' => 'Whitewood Inspection'],
            ['name' => 'Metal Inspection'],
            ['name' => 'Assembly Inspection'],
            ['name' => 'Edge Banding Inspection'],
            ['name' => 'Finishing Inspection'],
        ];

        foreach ($inspectionTypes as $type) {
            InspectionType::create($type);
        }
    }
}
