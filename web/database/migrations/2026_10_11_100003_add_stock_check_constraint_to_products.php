<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL CHECK constraint to ensure stock never goes negative
        // Note: MySQL 8.0.16+ supports CHECK constraints
        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_stock_non_negative CHECK (stock >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP CONSTRAINT chk_stock_non_negative');
    }
};
