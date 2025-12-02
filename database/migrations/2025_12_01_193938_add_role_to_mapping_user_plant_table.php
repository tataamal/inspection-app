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
        Schema::table('mapping_user_plant', function (Blueprint $table) {
            
            // Menambahkan kolom role setelah kolom sap_id
            $table->enum('role', ['admin', 'user'])
                  ->default('user')
                  ->after('sap_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapping_user_plant', function (Blueprint $table) {
            // Menghapus kolom jika di-rollback
            $table->dropColumn('role');
        });
    }
};
