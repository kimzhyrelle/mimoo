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
        Schema::table('order_items', function (Blueprint $table) {
            // Seller ID for analytics
            $table->foreignId('seller_id')->nullable()->after('product_id')->constrained('users')->onDelete('set null');
            
            // Snapshot of product name at time of purchase
            $table->string('product_name')->after('seller_id');
            
            // Index for seller queries
            $table->index('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropIndex(['seller_id']);
            $table->dropColumn(['seller_id', 'product_name']);
        });
    }
};
