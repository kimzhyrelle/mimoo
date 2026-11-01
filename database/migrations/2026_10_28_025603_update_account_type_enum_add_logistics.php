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
        // For MySQL, we need to alter the enum column
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('buyer', 'seller', 'logistics') NOT NULL DEFAULT 'buyer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove logistics from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('buyer', 'seller') NOT NULL DEFAULT 'buyer'");
    }
};
