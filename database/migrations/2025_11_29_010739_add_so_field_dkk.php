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
        Schema::table('history_quality_management', function (Blueprint $table) {
            $table->string('sales_order')->nullable()->after('order_number');
            $table->string('sales_item')->nullable()->after('sales_order');
            $table->string('buyer_name')->nullable()->after('sales_item');
            $table->string('customer_po')->nullable()->after('buyer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_quality_management', function (Blueprint $table) {
            $table->dropColumn(['sales_order', 'sales_item', 'buyer_name', 'customer_po']);
        });
    }
};
