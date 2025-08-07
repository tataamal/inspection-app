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
        Schema::create('inspection_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_type_id')
                  ->constrained('inspection_type') // FK untuk jenis inspeksi
                  ->onDelete('cascade');
            $table->string('header_name'); // Judul Header 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_headers');
    }
};
