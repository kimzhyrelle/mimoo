<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add district column for NCR addresses.
     * In NCR, the structure is: Region → District → City → Barangay
     * In other regions: Region → Province → City → Barangay
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add district after province, nullable since only NCR uses it
            $table->string('district')->nullable()->after('province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('district');
        });
    }
};
