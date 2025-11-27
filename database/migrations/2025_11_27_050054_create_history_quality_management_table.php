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
        Schema::create('history_quality_management', function (Blueprint $table) {
            $table->id();
            $table->string('prueflos')->index();
            $table->string('plant', 4)->index();
            $table->string('order_number')->nullable()->index();
            $table->string('material_code')->index();
            $table->string('material_desc')->nullable();
            $table->string('batch')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->string('uom', 5)->nullable();
            $table->string('inspector_username'); 
            $table->string('inspector_sap_id')->nullable(); 
            $table->string('inspector_name')->nullable(); 
            $table->string('ud_code'); 
            $table->string('ud_selected_set'); 
            $table->string('status'); 
            $table->text('sap_message')->nullable();
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
