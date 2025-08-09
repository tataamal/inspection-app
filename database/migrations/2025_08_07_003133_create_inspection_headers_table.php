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
            $table->unsignedBigInteger('inspection_type_id'); // singular

            $table->string('title');
            $table->timestamps();

            $table->foreign('inspection_type_id')
                ->references('id')
                ->on('inspection_types') // plural
                ->onDelete('cascade');
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
