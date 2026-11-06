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
        Schema::table('voucher_usages', function (Blueprint $table) {
            // Make order_id nullable (since voucher is claimed first, used later)
            $table->foreignId('order_id')->nullable()->change();
            
            // Make discount_amount nullable (only set when used)
            $table->decimal('discount_amount', 10, 2)->nullable()->change();
            
            // Add claimed_at timestamp (when user claims the voucher)
            $table->timestamp('claimed_at')->nullable()->after('discount_amount');
            
            // Rename used_at to be nullable (only set when actually used in checkout)
            $table->timestamp('used_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
            $table->decimal('discount_amount', 10, 2)->nullable(false)->change();
            $table->dropColumn('claimed_at');
            $table->timestamp('used_at')->nullable(false)->change();
        });
    }
};
