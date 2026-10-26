<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'packed' status to orders
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'packed', 'cancelled', 'failed', 'shipped', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'packed' status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'failed', 'shipped', 'completed') DEFAULT 'pending'");
    }
};
