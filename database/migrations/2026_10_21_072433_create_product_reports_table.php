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
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade'); // Buyer who reported
            $table->string('reason'); // e.g., 'misleading', 'inappropriate', 'counterfeit', 'other'
            $table->text('description')->nullable(); // Detailed explanation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable(); // Admin's notes when reviewing
            $table->timestamps();

            // Indexes
            $table->index('product_id');
            $table->index('reported_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
