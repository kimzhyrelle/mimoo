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
            // Add missing columns from Step 2 requirements
            $table->foreignId('buyer_id')->after('id')->constrained('users')->onDelete('cascade');
            $table->decimal('total', 10, 2)->after('buyer_id')->default(0);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->after('total')->default('pending');
            
            // Add indexes for performance
            $table->index('buyer_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropIndex(['buyer_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['buyer_id', 'total', 'status']);
        });
    }
};
