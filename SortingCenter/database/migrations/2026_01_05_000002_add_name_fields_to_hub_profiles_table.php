<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_profiles', function (Blueprint $table) {
            $table->string('first_name')->default('Sorting')->after('staff_title');
            $table->string('last_name')->default('Staff')->after('first_name');
            $table->string('middle_initial', 5)->nullable()->after('last_name');
        });
    }

    public function down(): void
    {
        Schema::table('hub_profiles', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'middle_initial']);
        });
    }
};
