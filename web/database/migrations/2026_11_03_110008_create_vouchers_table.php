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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MIMOO2026, FREESHIP, etc.
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']); // discount type
            $table->decimal('value', 10, 2); // discount value (percentage or fixed amount)
            $table->decimal('min_purchase', 10, 2)->default(0); // minimum purchase requirement
            $table->decimal('max_discount', 10, 2)->nullable(); // max discount cap for percentage
            $table->integer('usage_limit')->nullable(); // total usage limit (null = unlimited)
            $table->integer('usage_per_user')->default(1); // how many times per user
            $table->integer('usage_count')->default(0); // current usage count
            $table->dateTime('valid_from'); // start date
            $table->dateTime('valid_until'); // end date
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->json('terms_conditions')->nullable(); // T&C as JSON array
            $table->string('implemented_by')->nullable(); // Who created/implemented
            $table->timestamps();
            
            $table->index('code');
            $table->index('status');
            $table->index(['valid_from', 'valid_until']);
        });

        // Voucher usage tracking table
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2); // actual discount applied
            $table->timestamp('used_at');
            
            $table->index(['voucher_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('vouchers');
    }
};
