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
        Schema::create('history_quality_managements', function (Blueprint $table) {
            $table->id();
            $table->string('prueflos')->index();
            $table->string('plant', 4)->index();
            $table->string('order_number')->nullable()->index();
            $table->string('material_code')->index();
            $table->string('material_desc')->nullable();
            $table->string('batch')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->string('uom', 5)->nullable();
            $table->string('inspector_username'); // Username Login App
            $table->string('inspector_sap_id')->nullable(); // SAP ID
            $table->string('inspector_name')->nullable(); // Nama Asli
            $table->string('ud_code'); // Contoh: A
            $table->string('ud_selected_set'); // Contoh: 01
            $table->string('status'); // SUCCESS / ERROR
            $table->text('sap_message')->nullable(); // Pesan balik dari RFC SAP
            $table->json('full_lot_snapshot')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_quality_management');
    }
};
