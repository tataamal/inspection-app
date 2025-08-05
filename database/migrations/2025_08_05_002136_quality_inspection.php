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
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('prueflos')->unique();
            $table->string('charg');
            $table->date('inspection_date');
            $table->string('unit')->nullable();
            $table->string('location')->nullable();
            $table->string('ktexmat')->nullable();
            $table->string('dispo')->nullable();
            $table->string('mengeneinh')->nullable();
            $table->string('lagortchrg')->nullable();
            $table->string('matnr')->nullable();
            $table->string('kdpos')->nullable();
            $table->string('kdauf')->nullable();
            $table->json('inspection_items')->nullable();
            $table->text('cause_effect')->nullable();
            $table->text('correction')->nullable();
            $table->string('img_top_view')->nullable();
            $table->string('img_bottom_view')->nullable();
            $table->string('img_front_view')->nullable();
            $table->string('img_back_view')->nullable();
            $table->integer('aql_critical_found')->nullable();
            $table->integer('aql_critical_allowed')->nullable();
            $table->integer('aql_major_found')->nullable();
            $table->integer('aql_major_allowed')->nullable();
            $table->integer('aql_minor_found')->nullable();
            $table->integer('aql_minor_allowed')->nullable();
            $table->string('nik_qc')->nullable(); // hanya satu nik yang dipakai
            $table->string('qty_accepted')->nullable(); // menggantikan qty_unrestricted
            $table->string('qty_reject')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};
