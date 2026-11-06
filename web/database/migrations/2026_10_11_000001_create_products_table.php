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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0)->unsigned();
            $table->string('sku')->unique();
            $table->enum('status', ['active', 'draft', 'out_of_stock'])->default('draft');
            $table->json('images')->nullable(); // Array of image URLs
            $table->integer('main_image_index')->default(0); // Which image to show as primary
            $table->decimal('weight', 8, 2)->nullable(); // in kg
            $table->string('brand')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('seller_id');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
