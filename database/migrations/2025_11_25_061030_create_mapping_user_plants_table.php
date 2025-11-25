<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mapping_user_plant', function (Blueprint $table) {
            $table->id(); // Auto Increment ID
            
            $table->string('plant', 10)->index(); // Index ditambahkan
            $table->string('mrp', 10);
            $table->string('nik', 20)->index();   // Index ditambahkan
            $table->string('nama_karyawan', 255)->nullable();
            $table->string('sap_id', 50)->nullable(); 
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping_user_plants');
    }
};
