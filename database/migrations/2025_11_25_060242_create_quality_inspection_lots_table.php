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
        Schema::create('quality_inspection_lots', function (Blueprint $table) {
            // Primary Key (String, bukan Auto Increment ID)
            $table->string('PRUEFLOS', 20)->primary();

            // String Fields (VARCHAR)
            // Saya tambahkan ->nullable() agar aman jika datanya kosong
            $table->string('WERK', 10)->nullable()->index(); // Langsung index di sini juga bisa
            $table->string('ART', 10)->nullable();
            $table->string('HERKUNFT', 10)->nullable();
            $table->string('OBJNR', 30)->nullable();
            
            // Date & Time
            $table->date('ENSTEHDAT')->nullable();
            $table->string('ENTSTEZEIT', 10)->nullable(); // Disimpan sebagai string sesuai request lama
            
            $table->string('AUFNR', 20)->nullable();
            $table->string('DISPO', 10)->nullable()->index();
            $table->string('ARBPL', 20)->nullable();
            $table->string('KTEXT', 100)->nullable();
            $table->string('ARBID', 20)->nullable();
            $table->string('KUNNR', 20)->nullable();
            $table->string('LIFNR', 20)->nullable();
            $table->string('HERSTELLER', 20)->nullable();
            $table->string('EMATNR', 40)->nullable();
            $table->string('MATNR', 40)->nullable()->index();
            $table->string('CHARG', 20)->nullable();
            $table->string('LAGORTCHRG', 10)->nullable();
            $table->string('KDAUF', 20)->nullable();
            $table->string('KDPOS', 10)->nullable();
            $table->string('EBELN', 20)->nullable();
            $table->string('EBELP', 10)->nullable();
            $table->string('BLART', 10)->nullable();
            $table->string('MJAHR', 10)->nullable();
            $table->string('MBLNR', 20)->nullable();
            $table->string('ZEILE', 10)->nullable();
            
            $table->date('BUDAT')->nullable();
            
            $table->string('BWART', 10)->nullable();
            $table->string('WERKVORG', 10)->nullable();
            $table->string('LAGORTVORG', 10)->nullable();
            $table->string('LS_KDPOS', 10)->nullable();
            $table->string('LS_VBELN', 20)->nullable();
            $table->string('LS_POSNR', 10)->nullable();
            $table->string('LS_ROUTE', 20)->nullable();
            $table->string('LS_KUNAG', 20)->nullable();
            $table->string('LS_VKORG', 10)->nullable();
            $table->string('LS_KDMAT', 40)->nullable();
            $table->string('SPRACHE', 5)->nullable();
            $table->string('KTEXTMAT', 255)->nullable();
            
            // Decimals (DECIMAL 15,3)
            $table->decimal('LOSMENGE', 15, 3)->nullable();
            $table->string('MENGENEINH', 5)->nullable();
            $table->decimal('LMENGE01', 15, 3)->nullable();
            $table->decimal('LMENGE04', 15, 3)->nullable();
            $table->decimal('LMENGE07', 15, 3)->nullable();
            $table->decimal('LMENGEZUB', 15, 3)->nullable();
            
            $table->string('STAT34', 10)->nullable();
            $table->string('STAT35', 10)->nullable();
            $table->string('KTEXTLOS', 255)->nullable();
            $table->string('INSP_DOC_NUMBER', 30)->nullable();
            $table->string('AUFPL', 20)->nullable();
            $table->string('STATS', 20)->nullable();

            // Timestamps (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_lots');
    }
};
