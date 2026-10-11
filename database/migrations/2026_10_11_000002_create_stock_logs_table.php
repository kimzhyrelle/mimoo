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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('change'); // positive = added, negative = deducted
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->string('reason'); // e.g., "initial stock", "order", "manual adjustment", "restock"
            $table->string('reference_id')->nullable(); // e.g., order_id for "order" reason
            $table->timestamps();

            // Indexes for performance
            $table->index('product_id');
            $table->index('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
