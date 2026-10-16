<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_profiles', function (Blueprint $table) {
            $table->string('staff_name')->default('Sorting Staff')->after('name');
            $table->string('staff_initials', 4)->default('SC')->after('staff_name');
            $table->string('staff_title')->default('Sorting Staff')->after('staff_initials');
        });
    }

    public function down(): void
    {
        Schema::table('hub_profiles', function (Blueprint $table) {
            $table->dropColumn(['staff_name', 'staff_initials', 'staff_title']);
        });
    }
};
