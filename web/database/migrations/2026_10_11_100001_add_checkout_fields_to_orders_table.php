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
        Schema::table('orders', function (Blueprint $table) {
            // Drop old status enum to recreate with new values
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add new status with more states
            $table->enum('status', ['pending', 'paid', 'cancelled', 'failed', 'shipped', 'completed'])
                ->default('pending')
                ->after('total');
            
            // Payment fields
            $table->string('payment_status')->default('unpaid')->after('status');
            $table->string('payment_method')->nullable()->after('payment_status'); // cod, gcash, etc.
            
            // Shipping details
            $table->string('shipping_name')->nullable()->after('payment_method');
            $table->text('shipping_address')->nullable()->after('shipping_name');
            $table->string('shipping_phone', 20)->nullable()->after('shipping_address');
            
            // Idempotency - prevent double submission
            $table->string('idempotency_key', 64)->nullable()->unique()->after('shipping_phone');
            
            // Index for idempotency key
            $table->index('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'payment_status',
                'payment_method',
                'shipping_name',
                'shipping_address',
                'shipping_phone',
                'idempotency_key',
            ]);
            
            // Restore old status
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
        });
    }
};
