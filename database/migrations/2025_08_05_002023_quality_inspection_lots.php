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
            $table->id();
            $table->string('PRUEFLOS')->nullable();
            $table->string('WERK')->nullable();
            $table->string('ART')->nullable();
            $table->string('HERKUNFT')->nullable();
            $table->string('OBJNR')->nullable();
            $table->date('ENSTEHDAT')->nullable();
            $table->string('ENTSTEZEIT')->nullable();
            $table->string('AUFNR')->nullable();
            $table->string('DISPO')->nullable();
            $table->string('ARBPL')->nullable();
            $table->string('KTEXT')->nullable();
            $table->string('ARBID')->nullable();
            $table->string('KUNNR')->nullable();
            $table->string('LIFNR')->nullable();
            $table->string('HERSTELLER')->nullable();
            $table->string('EMATNR')->nullable();
            $table->string('MATNR')->nullable();
            $table->string('CHARG')->nullable();
            $table->string('LAGORTCHRG')->nullable();
            $table->string('KDAUF')->nullable();
            $table->string('KDPOS')->nullable();
            $table->string('EBELN')->nullable();
            $table->string('EBELP')->nullable();
            $table->string('BLART')->nullable();
            $table->string('MJAHR')->nullable();
            $table->string('MBLNR')->nullable();
            $table->string('ZEILE')->nullable();
            $table->date('BUDAT')->nullable();
            $table->string('BWART')->nullable();
            $table->string('WERKVORG')->nullable();
            $table->string('LAGORTVORG')->nullable();
            $table->string('LS_KDPOS')->nullable();
            $table->string('LS_VBELN')->nullable();
            $table->string('LS_POSNR')->nullable();
            $table->string('LS_ROUTE')->nullable();
            $table->string('LS_KUNAG')->nullable();
            $table->string('LS_VKORG')->nullable();
            $table->string('LS_KDMAT')->nullable();
            $table->string('SPRACHE')->nullable();
            $table->string('KTEXTMAT')->nullable();
            $table->float('LOSMENGE')->nullable();
            $table->string('MENGENEINH')->nullable();
            $table->float('LMENGE01')->nullable();
            $table->float('LMENGE04')->nullable();
            $table->float('LMENGE07')->nullable();
            $table->float('LMENGEZUB')->nullable();
            $table->string('STAT34')->nullable();
            $table->string('STAT35')->nullable();
            $table->string('KTEXTLOS')->nullable();
            $table->string('INSP_DOC_NUMBER')->nullable();
            $table->string('AUFPL')->nullable();
            $table->string('STATS')->nullable();
            $table->timestamps(); // created_at & updated_at
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
